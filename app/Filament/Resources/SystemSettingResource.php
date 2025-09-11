<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingResource\Pages;
use App\Filament\Resources\SystemSettingResource\RelationManagers;
use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SystemSettingResource extends Resource
{
    protected static ?string $model = SystemSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'إعدادات النظام';
    protected static ?string $modelLabel = 'إعداد نظام';
    protected static ?string $pluralModelLabel = 'إعدادات النظام';
    protected static ?string $navigationGroup = ' إدارة النظام الشركة';
    protected static ?int $navigationSort = 0;
    protected static bool $shouldRegisterNavigation = true;
    // إظهار الصفحة للمدير الأساسي فقط
    public static function canViewAny(): bool
    {
        return false;
    }
    
    public static function canCreate(): bool
    {
        return false;
    }
    
    public static function canEdit($record): bool
    {
        return false;
    }
    
    public static function canDelete($record): bool
    {
        return false;
    }
    
    public static function canDeleteAny(): bool
    {
        return false;
    }


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
                Forms\Components\TextInput::make('sys_earlyexit'),
                Forms\Components\TextInput::make('sys_earlycall'),
                Forms\Components\TextInput::make('sys_return_call'),
                Forms\Components\TextInput::make('sys_exit_togat'),
                Forms\Components\TextInput::make('sys_cust_code'),
                Forms\Components\TextInput::make('sys_cdate'),
                Forms\Components\TextInput::make('sys_udate'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sys_earlyexit')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sys_earlycall')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sys_return_call')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sys_exit_togat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sys_cust_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sys_cdate')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sys_udate')
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
            'index' => Pages\SystemSettings::route('/'),
        ];
    }
}
