<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LicenseResource\Pages;
use App\Models\License;
use App\Models\School;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LicenseResource extends Resource
{
    protected static ?string $model = License::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationLabel = 'التراخيص';
    protected static ?string $modelLabel = 'ترخيص';
    protected static ?string $pluralModelLabel = 'التراخيص';
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
        return $form
            ->schema([
                Forms\Components\Select::make('school_id')
                    ->label('المدرسة')
                    ->options(
                        School::all()->mapWithKeys(function ($school) {
                            return [$school->id => $school->name_ar ?? 'بدون اسم'];
                        })
                    )
                    ->required(),

                Forms\Components\Select::make('subscription_id')
                    ->label('الاشتراك')
                    ->options(
                        Subscription::all()->mapWithKeys(function ($sub) {
                            return [$sub->id => $sub->name ?? 'بدون اسم'];
                        })
                    )
                    ->required(),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->label('تاريخ البداية')
                    ->required(),

                Forms\Components\DateTimePicker::make('ends_at')
                    ->label('تاريخ الانتهاء')
                    ->required(),

            Forms\Components\Hidden::make('created_by')
                ->default(fn () =>  auth()->user()->id)
                ->dehydrated(true) // إجباراً يترسل
                ->required(),

                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('school.name_ar')
                    ->label('المدرسة')
                    ->searchable(),

                Tables\Columns\TextColumn::make('subscription.name')
                    ->label('الاشتراك')
                    ->searchable(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('تاريخ البداية')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('تاريخ الانتهاء')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('تم الإنشاء بواسطة')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
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
            'index' => Pages\ListLicenses::route('/'),
            'create' => Pages\CreateLicense::route('/create'),
            'edit' => Pages\EditLicense::route('/{record}/edit'),
        ];
    }
}
