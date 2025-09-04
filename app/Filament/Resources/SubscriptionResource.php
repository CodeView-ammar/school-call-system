<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'الاشتراكات';
    protected static ?string $modelLabel = 'اشتراك';
    protected static ?string $pluralModelLabel = 'الاشتراكات';
    protected static ?string $navigationGroup = 'إدارة النظام';
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
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم الاشتراك')
                    ->required(),

                Forms\Components\Select::make('type_id')
                    ->label('نوع الاشتراك')
                    ->options(SubscriptionType::all()->pluck('name_en', 'id'))
                    ->required(),

                Forms\Components\TextInput::make('price')
                    ->label('السعر')
                    ->required()
                    ->numeric(),

                Forms\Components\TextInput::make('max_students')
                    ->label('عدد الطلاب الأقصى')
                    ->numeric(),

                Forms\Components\TextInput::make('max_calls')
                    ->label('عدد المكالمات الأقصى')
                    ->numeric(),

                Forms\Components\TextInput::make('max_buses')
                    ->label('عدد الحافلات الأقصى')
                    ->numeric(),

                Forms\Components\TextInput::make('max_classes')
                    ->label('عدد الفصول الأقصى')
                    ->numeric(),

                Forms\Components\TextInput::make('max_users')
                    ->label('عدد المستخدمين الأقصى')
                    ->numeric(),

                Forms\Components\TextInput::make('max_drivers')
                    ->label('عدد السائقين الأقصى')
                    ->numeric(),

                Forms\Components\TextInput::make('max_branches')
                    ->label('عدد فروع المدارس الأقصى')
                    ->numeric(),

                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الاشتراك')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type.name_en')
                    ->label('نوع الاشتراك')
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_students')
                    ->label('عدد الطلاب الأقصى'),

                Tables\Columns\TextColumn::make('max_calls')
                    ->label('عدد المكالمات الأقصى'),

                Tables\Columns\TextColumn::make('max_buses')
                    ->label('عدد الحافلات الأقصى'),

                Tables\Columns\TextColumn::make('max_classes')
                    ->label('عدد الفصول الأقصى'),

                Tables\Columns\TextColumn::make('max_users')
                    ->label('عدد المستخدمين الأقصى'),

                Tables\Columns\TextColumn::make('max_drivers')
                    ->label('عدد السائقين الأقصى'),

                Tables\Columns\TextColumn::make('max_branches')
                    ->label('عدد فروع المدارس الأقصى'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
