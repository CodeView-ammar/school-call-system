<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionTypeResource\Pages;
use App\Filament\Resources\SubscriptionTypeResource\RelationManagers;
use App\Models\SubscriptionType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubscriptionTypeResource extends Resource
{

    protected static ?string $model = SubscriptionType::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'أنواع الاشتراك';
    protected static ?string $modelLabel = 'نوع الاشتراك';
    protected static ?string $pluralModelLabel = 'أنواع الاشتراك';
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationGroup = ' إدارة النظام الشركة';
// إظهار الصفحة للمدير الأساسي فقط
    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->user_type === 'super_admin';
    }
    
    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->user_type === 'super_admin';
    }
    
    public static function canEdit($record): bool
    {
        return auth()->check() && auth()->user()->user_type === 'super_admin';
    }
    
    public static function canDelete($record): bool
    {
        return auth()->check() && auth()->user()->user_type === 'super_admin';
    }
    
    public static function canDeleteAny(): bool
    {
        return auth()->check() && auth()->user()->user_type === 'super_admin';
    }

    public static function form(Form $form): Form
    {
         return $form->schema([
            Forms\Components\TextInput::make('name_ar')
                ->label('الاسم بالعربية')
                ->required()
                ->maxLength(100),

            Forms\Components\TextInput::make('name_en')
                ->label('الاسم بالإنجليزية')
                ->required()
                ->maxLength(100),
        ]);
    }

    public static function table(Table $table): Table
    {
       return $table->columns([
            Tables\Columns\TextColumn::make('name_ar')->label('الاسم بالعربية')->searchable(),
            Tables\Columns\TextColumn::make('name_en')->label('الاسم بالإنجليزية')->searchable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
        ])
        ->filters([])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSubscriptionTypes::route('/'),
            'create' => Pages\CreateSubscriptionType::route('/create'),
            'edit' => Pages\EditSubscriptionType::route('/{record}/edit'),
        ];
    }
}
