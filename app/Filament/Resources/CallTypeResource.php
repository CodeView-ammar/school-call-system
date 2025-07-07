<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CallTypeResource\Pages;
use App\Filament\Resources\CallTypeResource\RelationManagers;
use App\Models\CallType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CallTypeResource extends Resource
{
    protected static ?string $model = CallType::class;

    protected static ?string $navigationIcon = 'heroicon-o-phone';
    protected static ?string $navigationLabel = 'أنواع المكالمات';
    protected static ?string $modelLabel = 'نوع مكالمة';
    protected static ?string $pluralModelLabel = 'أنواع المكالمات';
    protected static ?string $navigationGroup = 'إدارة المكالمات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('ctype_name_ar')
                            ->label('اسم نوع المكالمة (عربي)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('ctype_name_eng')
                            ->label('اسم نوع المكالمة (إنجليزي)')
                            ->maxLength(255),
                    ]),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Toggle::make('ctype_isactive')
                            ->label('نشط')
                            ->default(true),
                        Forms\Components\TextInput::make('ctype_cust_code')
                            ->label('كود العميل')
                            ->maxLength(255),
                    ]),
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
                Tables\Columns\TextColumn::make('ctype_cust_code')
                    ->label('كود العميل')
                    ->searchable(),
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
            'index' => Pages\ListCallTypes::route('/'),
            'create' => Pages\CreateCallType::route('/create'),
            'edit' => Pages\EditCallType::route('/{record}/edit'),
        ];
    }
}
