<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppVersionResource\Pages;
use App\Filament\Resources\AppVersionResource\RelationManagers;
use App\Models\AppVersion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Http\Controllers\Api\StudentExportController;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;

class AppVersionResource extends Resource
{

    protected static ?string $model = AppVersion::class;


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    
    protected static ?string $navigationLabel = 'إدارة الاصدارات';
    
    protected static ?string $modelLabel = 'الاصدارات';
    
    protected static ?string $pluralModelLabel = 'إدارة الاصدارات';
    
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
    {   return $form->schema([
        Select::make('platform')
            ->options([
                'android' => 'Android',
                'ios' => 'iOS',
            ])
            ->required(),

        TextInput::make('version')
            ->label('رقم النسخة')
            ->required(),

        Toggle::make('force_update')
            ->label('تحديث إجباري؟'),

        Textarea::make('message')
            ->label('ملاحظات/تغييرات النسخة')
            ->rows(5),
    ]);
    }

    public static function table(Table $table): Table
    {
     return $table->columns([
        Tables\Columns\TextColumn::make('platform')->label('المنصة'),
        Tables\Columns\TextColumn::make('version')->label('الإصدار'),
        Tables\Columns\BooleanColumn::make('force_update')->label('إجباري'),
        Tables\Columns\TextColumn::make('created_at')->label('تم الإنشاء')->dateTime(),
    ])
    ->defaultSort('id', 'desc');
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
            'index' => Pages\ListAppVersions::route('/'),
            'create' => Pages\CreateAppVersion::route('/create'),
            'edit' => Pages\EditAppVersion::route('/{record}/edit'),
        ];
    }
}
