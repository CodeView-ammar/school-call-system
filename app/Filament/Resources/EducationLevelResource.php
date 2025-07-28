<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EducationLevelResource\Pages;
use App\Filament\Resources\EducationLevelResource\RelationManagers;
use App\Models\EducationLevel;
use App\Models\School;
use Filament\Forms\Components\Select;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EducationLevelResource extends Resource
{
    protected static ?string $model = EducationLevel::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';
    
    protected static ?string $navigationLabel = 'المراحل الدراسية';
    
    protected static ?string $modelLabel = 'مرحلة الدراسية';
    
    protected static ?string $pluralModelLabel = 'المراحل الدراسية';
    
    protected static ?string $navigationGroup = 'إدارة المدارس';
    
    protected static ?int $navigationSort = 2;
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
                        Forms\Components\TextInput::make('name_ar')
                            ->label('اسم المرحلة (عربي)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name_en')
                            ->label('Stage Name (English)')
                            ->required()
                            ->maxLength(255),
                    ]),
                Forms\Components\TextInput::make('short_name')
                    ->label('الاسم المختصر')
                    ->maxLength(10),
                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
            auth()->user()?->school_id === null
            ? Select::make('school_id')
                ->label('المدرسة')
                ->relationship('school', 'name_ar')
                ->required()
                ->searchable()
                ->preload()
            : Forms\Components\Hidden::make('school_id')
                ->default(auth()->user()->school_id)
                ->dehydrated(true)
                ->required(),
                    
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('school.name_ar')
                    ->label('المدرسة')
                    ->sortable()
                    ->searchable()
                    ->visible(fn () => auth()->user()?->school_id === null),
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('اسم المرحلة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name_en')
                    ->label('English Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('short_name')
                    ->label('الاسم المختصر')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->native(false),
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
            'index' => Pages\ListEducationLevels::route('/'),
            'edit' => Pages\EditEducationLevel::route('/{record}/edit'),
        ];
    }
}
