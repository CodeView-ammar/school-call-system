<?php
namespace App\Filament\Resources;

use App\Filament\Resources\WeekDayResource\Pages;
use App\Models\WeekDay;
use App\Models\School;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WeekDayResource extends Resource
{
    protected static ?string $model = WeekDay::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'أيام الأسبوع';
    protected static ?string $modelLabel = 'يوم أسبوع';
    protected static ?string $pluralModelLabel = 'أيام الأسبوع';
    protected static ?string $navigationGroup = 'إدارة التوقيت';

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Section::make('معلومات اليوم')
                ->description('تعيين أيام الأسبوع وأوقات العمل')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Select::make('school_id')
                                ->label('المدرسة')
                                ->options(fn () => School::all()->pluck('name_ar', 'id'))
                                ->default(auth()->user()?->school_id)
                                ->disabled(fn () => auth()->user()?->school_id !== null)
                                ->hidden(fn () => auth()->user()?->school_id !== null)
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(fn ($state, callable $set) => $set('branch_id', null)),

                            Forms\Components\Select::make('branch_id')
                                ->label('الفرع')
                                ->options(function (callable $get) {
                                    $schoolId = $get('school_id') ?? auth()->user()?->school_id;
                                    if (!$schoolId) {
                                        return [];
                                    }
                                    return Branch::where('school_id', $schoolId)->pluck('name_ar', 'id');
                                })
                                ->required()
                                ->reactive(),
                        ]),

                    Forms\Components\Group::make()
                        ->schema([
                            // ✅ يظهر فقط عند الإنشاء
                            Forms\Components\CheckboxList::make('days')
                                ->label('الأيام')
                                ->options([
                                    'الأحد' => 'الأحد',
                                    'الاثنين' => 'الاثنين',
                                    'الثلاثاء' => 'الثلاثاء',
                                    'الأربعاء' => 'الأربعاء',
                                    'الخميس' => 'الخميس',
                                    'الجمعة' => 'الجمعة',
                                    'السبت' => 'السبت',
                                ])
                                ->visible(fn ($livewire) => $livewire instanceof \App\Filament\Resources\WeekDayResource\Pages\CreateWeekDay)
                                ->required(),

                            // ✅ يظهر فقط عند التعديل
                            Forms\Components\Select::make('day')
                                ->label('اليوم')
                                ->options([
                                    'الأحد' => 'الأحد',
                                    'الاثنين' => 'الاثنين',
                                    'الثلاثاء' => 'الثلاثاء',
                                    'الأربعاء' => 'الأربعاء',
                                    'الخميس' => 'الخميس',
                                    'الجمعة' => 'الجمعة',
                                    'السبت' => 'السبت',
                                ])
                                ->disabled() // ⛔ يمنع تعديله أثناء التعديل (اختياري)
                                ->visible(fn ($livewire) => $livewire instanceof \App\Filament\Resources\WeekDayResource\Pages\EditWeekDay)
                                ->required(),

                            Forms\Components\TimePicker::make('time_from')
                                ->label('من الساعة')
                                ->required()
                                ->seconds(false),

                            Forms\Components\TimePicker::make('time_to')
                                ->label('إلى الساعة')
                                ->required()
                                ->seconds(false),
                        ]),
                ]),

            Forms\Components\Section::make('إعدادات إضافية')
                ->description('إعدادات اختيارية للنظام')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Toggle::make('day_inactive')
                                ->label('غير نشط')
                                ->default(true),
                ]),

                ]),
        ]);
}


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('school.name_ar')
                    ->label('المدرسة')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('branch.name_ar')
                    ->label('الفرع')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('day')
                    ->label('اليوم')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('time_from')
                    ->label('من الساعة')
                    ->time('H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('time_to')
                    ->label('إلى الساعة')
                    ->time('H:i')
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('day_inactive')
                    ->label('نشط')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),
                    
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('school_id')
                    ->label('المدرسة')
                    ->relationship('school', 'name_ar'),

                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('الفرع')
                    ->relationship('branch', 'name_ar'),

                Tables\Filters\SelectFilter::make('day')
                    ->label('اليوم')
                    ->options([
                        'الأحد' => 'الأحد',
                        'الاثنين' => 'الاثنين',
                        'الثلاثاء' => 'الثلاثاء',
                        'الأربعاء' => 'الأربعاء',
                        'الخميس' => 'الخميس',
                        'الجمعة' => 'الجمعة',
                        'السبت' => 'السبت',
                    ]),
                    
                Tables\Filters\TernaryFilter::make('day_inactive')
                    ->label('الحالة')
                    ->trueLabel('غير نشط')
                    ->falseLabel('نشط')
                    ->nullable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWeekDays::route('/'),
            'create' => Pages\CreateWeekDay::route('/create'),
            'edit' => Pages\EditWeekDay::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // إذا كان المستخدم مدير مدرسة، اعرض البيانات الخاصة بمدرسته فقط
        if (auth()->user()?->school_id) {
            $query->where('school_id', auth()->user()->school_id);
        }
        
        return $query;
    }
}
