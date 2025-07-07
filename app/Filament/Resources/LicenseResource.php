<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LicenseResource\Pages;
use App\Filament\Resources\LicenseResource\RelationManagers;
use App\Models\License;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LicenseResource extends Resource
{
    protected static ?string $model = License::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationLabel = 'التراخيص';
    protected static ?string $modelLabel = 'ترخيص';
    protected static ?string $pluralModelLabel = 'التراخيص';
    protected static ?string $navigationGroup = 'إدارة النظام';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('lic_start_at')
                    ->required(),
                Forms\Components\TextInput::make('lic_end_at')
                    ->required(),
                Forms\Components\TextInput::make('lic_by_user')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('lic_cdate'),
                Forms\Components\TextInput::make('lic_udate'),
                Forms\Components\TextInput::make('lic_cust_code'),
                Forms\Components\TextInput::make('lic_isactive'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lic_start_at')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lic_end_at')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lic_by_user')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lic_cdate')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lic_udate')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lic_cust_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lic_isactive')
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
            'index' => Pages\ListLicenses::route('/'),
            'create' => Pages\CreateLicense::route('/create'),
            'edit' => Pages\EditLicense::route('/{record}/edit'),
        ];
    }
}
