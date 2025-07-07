<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HolidayResource\Pages;
use App\Filament\Resources\HolidayResource\RelationManagers;
use App\Models\Holiday;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HolidayResource extends Resource
{
    protected static ?string $model = Holiday::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'العطل';
    protected static ?string $modelLabel = 'عطلة';
    protected static ?string $pluralModelLabel = 'العطل';
    protected static ?string $navigationGroup = 'إدارة التوقيت';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('holiday_name_ar')
                            ->label('اسم العطلة (عربي)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('holiday_name_en')
                            ->label('اسم العطلة (إنجليزي)')
                            ->maxLength(255),
                    ]),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\DatePicker::make('holiday_from_date')
                            ->label('تاريخ بداية العطلة')
                            ->required()
                            ->native(false),
                        Forms\Components\DatePicker::make('holiday_to_date')
                            ->label('تاريخ نهاية العطلة')
                            ->required()
                            ->native(false)
                            ->after('holiday_from_date'),
                    ]),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Toggle::make('holiday_isactive')
                            ->label('نشط')
                            ->default(true),
                        Forms\Components\TextInput::make('holiday_cust_code')
                            ->label('كود العميل')
                            ->maxLength(255),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('holiday_name_ar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('holiday_name_en')
                    ->searchable(),
                Tables\Columns\TextColumn::make('holiday_from_date')
                    ->searchable(),
                Tables\Columns\TextColumn::make('holiday_to_date')
                    ->searchable(),
                Tables\Columns\TextColumn::make('holiday_isactive')
                    ->searchable(),
                Tables\Columns\TextColumn::make('holiday_cust_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('holiday_cdate')
                    ->searchable(),
                Tables\Columns\TextColumn::make('holiday_udate')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListHolidays::route('/'),
            'create' => Pages\CreateHoliday::route('/create'),
            'edit' => Pages\EditHoliday::route('/{record}/edit'),
        ];
    }
}
