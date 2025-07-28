<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusResource\Pages;
use App\Filament\Resources\BusResource\RelationManagers;
use App\Models\Bus;
use App\Models\Branch;
use App\Models\Driver;
use App\Models\Supervisor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BusResource extends Resource
{
    protected static ?string $model = Bus::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    
    protected static ?string $navigationLabel = 'الحافلات';
    
    protected static ?string $modelLabel = 'حافلة';
    
    protected static ?string $pluralModelLabel = 'الحافلات';
    
    protected static ?string $navigationGroup = 'إدارة النقل';
    
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()?->school_id) {
            $query->where('school_id', auth()->user()->school_id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات الحافلة الأساسية')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('school_id')
                                    ->label('المدرسة')
                                    ->options(fn () => \App\Models\School::pluck('name_ar', 'id'))
                                    ->default(auth()->user()?->school_id)
                                    ->hidden(fn () => auth()->user()?->school_id !== null)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('branch_id', null);
                                        $set('driver_id', null); // إعادة تعيين السائق عند تغيير المدرسة
                                    }),

                                Forms\Components\Select::make('branch_id')
                                    ->label('الفرع')
                                    ->options(function (callable $get) {
                                        $schoolId = auth()->user()?->school_id ?? $get('school_id');
                                        if (!$schoolId) {
                                            return [];
                                        }
                                        return Branch::where('school_id', $schoolId)->pluck('name_ar', 'id');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('driver_id')
                                    ->label('السائق')
                                    ->options(function (callable $get) {
                                        $schoolId = auth()->user()?->school_id ?? $get('school_id');
                                        if (!$schoolId) {
                                            return [];
                                        }
                                        return \App\Models\User::where('user_type', 'driver')
                                            ->where('school_id', $schoolId)
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->required(false)
                                    ->reactive()  // تجعلها تتفاعل مع تغيرات النموذج
                                    ->helperText('ملاحظة: عند إضافة مستخدم كسائق، تأكد من منحه صلاحيات السائق في النظام.'),

                        Forms\Components\Select::make('supervisor_id')
                            ->label('المشرف')
                            ->options(\App\Models\User::where('user_type', 'supervisor')->orwhere('user_type', 'driver')->pluck('name', 'id'))
                            ->searchable()
                            ->required(false)
                            ->helperText('ملاحظة: عند إضافة مستخدم كمشرف، تأكد من منحه صلاحيات المشرف في النظام.'),
                                Forms\Components\TextInput::make('number')
                                    ->label('رقم الحافلة')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50),
                                    
                                Forms\Components\TextInput::make('plate_number')
                                    ->label('رقم اللوحة')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(20),
                                    

                                Forms\Components\TextInput::make('capacity')
                                    ->label('السعة')
                                    ->numeric()
                                    ->required()
                                    ->minValue(10)
                                    ->maxValue(100),
                            ]),
                    ]),
                    
                Forms\Components\Section::make('بيانات الحافلة التفصيلية')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('model')
                                    ->label('الموديل')
                                    ->maxLength(100),
                                    
                                Forms\Components\TextInput::make('year')
                                    ->label('سنة الصنع')
                                    ->numeric()
                                    ->minValue(2000)
                                    ->maxValue(date('Y')),
                                    
                                Forms\Components\TextInput::make('color')
                                    ->label('اللون')
                                    ->maxLength(50),
                                    
                                Forms\Components\Select::make('fuel_type')
                                    ->label('نوع الوقود')
                                    ->options([
                                        'gasoline' => 'بنزين',
                                        'diesel' => 'ديزل',
                                        'hybrid' => 'هجين',
                                        'electric' => 'كهربائي',
                                    ])
                                    ->default('diesel'),
                            ]),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('engine_number')
                                    ->label('رقم المحرك')
                                    ->maxLength(50),
                                    
                                Forms\Components\TextInput::make('chassis_number')
                                    ->label('رقم الشاسيه')
                                    ->maxLength(50),
                            ]),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('وصف إضافي')
                            ->rows(3),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('مفعل')
                            ->default(true),
                    ]),
                    
                Forms\Components\Section::make('معلومات التأمين والصيانة')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('insurance_expiry')
                                    ->label('تاريخ انتهاء التأمين')
                                    ->native(false),
                                    
                                Forms\Components\DatePicker::make('license_expiry')
                                    ->label('تاريخ انتهاء الرخصة')
                                    ->native(false),
                                    
                                Forms\Components\DatePicker::make('last_maintenance')
                                    ->label('آخر صيانة')
                                    ->native(false),
                                    
                                Forms\Components\DatePicker::make('next_maintenance')
                                    ->label('الصيانة القادمة')
                                    ->native(false),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('رقم الحافلة')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('plate_number')
                    ->label('رقم اللوحة')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('school.name_ar')
                    ->label('المدرسة')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()?->school_id === null),
                
                Tables\Columns\TextColumn::make('branch.name_ar')
                    ->label('الفرع')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('capacity')
                    ->label('السعة')
                    ->alignCenter()
                    ->badge()
                    ->color('success'),
                    

                    
                Tables\Columns\TextColumn::make('available_seats')
                    ->label('المقاعد المتاحة')
                    ->getStateUsing(fn ($record) => $record->capacity - $record->students_count)
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state) => $state > 5 ? 'success' : ($state > 0 ? 'warning' : 'danger')),
                    
                Tables\Columns\TextColumn::make('model')
                    ->label('الموديل')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('year')
                    ->label('سنة الصنع')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                Tables\Columns\TextColumn::make('insurance_expiry')
                    ->label('انتهاء التأمين')
                    ->date('Y-m-d')
                    ->sortable()
                    ->color(fn ($state) => $state && $state <= now()->addDays(30) ? 'danger' : 'success')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('الفرع')
                    ->options(Branch::query()->pluck('name_ar', 'id'))
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('fuel_type')
                    ->label('نوع الوقود')
                    ->options([
                        'gasoline' => 'بنزين',
                        'diesel' => 'ديزل',
                        'hybrid' => 'هجين',
                        'electric' => 'كهربائي',
                    ]),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueLabel('مفعل')
                    ->falseLabel('غير مفعل')
                    ->native(false),
                    
                Tables\Filters\Filter::make('insurance_expiry_soon')
                    ->label('التأمين ينتهي قريباً')
                    ->query(fn (Builder $query): Builder => $query->where('insurance_expiry', '<=', now()->addDays(30))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
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
            ->defaultSort('number', 'asc');
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
            'index' => Pages\ListBuses::route('/'),
            'create' => Pages\CreateBus::route('/create'),
            'edit' => Pages\EditBus::route('/{record}/edit'),
        ];
    }
}
