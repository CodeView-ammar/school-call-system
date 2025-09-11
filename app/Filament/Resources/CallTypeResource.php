<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CallTypeResource\Pages;
use App\Models\CallType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CallTypeResource extends Resource
{
    protected static ?string $model = CallType::class;

    protected static ?string $navigationIcon = 'heroicon-o-phone';
    protected static ?string $navigationLabel = 'أنواع الندائات';
    protected static ?string $modelLabel = 'نوع النداء';
    protected static ?string $pluralModelLabel = 'أنواع الندائات';
    protected static ?int $navigationSort = 1;
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
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('ctype_name_ar')
                            ->label('اسم نوع النداء (عربي)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('ctype_name_eng')
                            ->label('اسم نوع النداء (إنجليزي)')
                            ->maxLength(255),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Toggle::make('ctype_isactive')
                            ->label('نشط')
                            ->default(true),
                            auth()->user()?->school_id === null
                    ? Forms\Components\Select::make('school_id')
                        ->label('المدرسة')
                        ->relationship('school', 'name_ar')
                        ->required()
                        ->searchable()
                        ->preload()
                    : Forms\Components\Hidden::make('school_id')
                        ->default(auth()->user()->school_id)
                        ->dehydrated(true)
                        ->required(),
                            ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ctype_name_ar')
                    ->label('الاسم بالعربية')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ctype_name_eng')
                    ->label('الاسم بالإنجليزية')
                    ->searchable(),
                Tables\Columns\IconColumn::make('ctype_isactive')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
            Tables\Columns\TextColumn::make('school.name_ar')
                ->label('المدرسة')
                ->searchable()
                ->sortable()
                ->visible(fn () => auth()->user()?->school_id === null),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
           ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
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
            'index' => Pages\ListCallTypes::route('/'),
            // 'create' => Pages\CreateCallType::route('/create'),
            // 'edit' => Pages\EditCallType::route('/{record}/edit'),
        ];
    }
}
