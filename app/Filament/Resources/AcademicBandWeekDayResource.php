<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AcademicBandWeekDayResource\Pages;
use App\Models\AcademicBandWeekDay;
use App\Models\School;
use App\Models\AcademicBand;
use App\Models\WeekDay;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AcademicBandWeekDayResource extends Resource
{
    protected static ?string $model = AcademicBandWeekDay::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'إدارة المدارس';

    protected static ?string $modelLabel = 'جدولة الفرق الدراسية';

    protected static ?string $pluralModelLabel = 'جدولة الفرق الدراسية';

    protected static ?int $navigationSort = 5;

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
            Forms\Components\Select::make('school_id')
                ->label('المدرسة')
                ->relationship('school', 'name_ar')
                ->required()
                ->default(fn () => auth()->user()?->school_id)
                ->disabled(fn () => auth()->user()?->school_id !== null),

            Forms\Components\Select::make('academic_band_id')
                ->label('الفرقة الدراسية')
                ->options(function (callable $get) {
                    $schoolId = $get('school_id') ?? auth()->user()?->school_id;
                    if (!$schoolId) return [];

                    return AcademicBand::where('school_id', $schoolId)
                        ->where('is_active', true)
                        ->pluck('name_ar', 'id');
                })
                ->required()
                ->searchable(),

            Forms\Components\Select::make('week_day_id')
                ->label('يوم الأسبوع')
                ->options(function (callable $get) {
                    $schoolId = $get('school_id') ?? auth()->user()?->school_id;
                    if (!$schoolId) return [];

                    return WeekDay::where('school_id', $schoolId)
                        ->where('day_inactive', '!=', 1)
                        ->pluck('day', 'day_id');
                })
                ->required()
                ->searchable(),

            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TimePicker::make('start_time')
                        ->label('وقت البداية')
                        ->seconds(false)
                        ->required(),

                    Forms\Components\TimePicker::make('end_time')
                        ->label('وقت النهاية')
                        ->seconds(false)
                        ->required()
                        ->after('start_time'),
                ]),

            Forms\Components\Toggle::make('is_active')
                ->label('نشط')
                ->default(true),

            Forms\Components\Textarea::make('notes')
                ->label('ملاحظات')
                ->rows(3)
                ->columnSpanFull(),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('school.name_ar')
                    ->label('المدرسة')
                    ->searchable()
                    ->visible(fn () => auth()->user()?->school_id === null),

                Tables\Columns\TextColumn::make('academicBand.name_ar')
                    ->label('الفرقة الدراسية')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('weekDay.day')
                    ->label('يوم الأسبوع')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('وقت البداية')
                    ->time('H:i')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('وقت النهاية')
                    ->time('H:i')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('academic_band_id')
                    ->label('الفرقة الدراسية')
                    ->relationship('academicBand', 'name_ar'),

                Tables\Filters\SelectFilter::make('week_day_id')
                    ->label('يوم الأسبوع')
                    ->relationship('weekDay', 'day'),

                Tables\Filters\Filter::make('is_active')
                    ->label('النشطة فقط')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('تفعيل المحدد')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_active' => true]));
                        }),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('إلغاء تفعيل المحدد')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_active' => false]));
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAcademicBandWeekDays::route('/'),
            'create' => Pages\CreateAcademicBandWeekDay::route('/create'),
            'edit' => Pages\EditAcademicBandWeekDay::route('/{record}/edit'),
        ];
    }
}