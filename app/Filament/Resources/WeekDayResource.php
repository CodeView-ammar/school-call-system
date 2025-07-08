<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WeekDayResource\Pages;
use App\Filament\Resources\WeekDayResource\RelationManagers;
use App\Models\WeekDay;
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
                Forms\Components\TextInput::make('day')
                    ->required(),
                Forms\Components\TextInput::make('time_to')
                    ->required(),
                Forms\Components\TextInput::make('time_from')
                    ->required(),
                Forms\Components\TextInput::make('day_inactive'),
                Forms\Components\TextInput::make('branch_code'),
                Forms\Components\TextInput::make('customer_code'),
                Forms\Components\TextInput::make('band_id')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('day')
                    ->searchable(),
                Tables\Columns\TextColumn::make('time_to'),
                Tables\Columns\TextColumn::make('time_from'),
                Tables\Columns\TextColumn::make('day_inactive')
                    ->searchable(),
                Tables\Columns\TextColumn::make('branch_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('band_id')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListWeekDays::route('/'),
            'create' => Pages\CreateWeekDay::route('/create'),
            'edit' => Pages\EditWeekDay::route('/{record}/edit'),
        ];
    }
}
