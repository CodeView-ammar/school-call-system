<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeClassResource\Pages;
use App\Filament\Resources\GradeClassResource\RelationManagers;
use App\Models\GradeClass;
use App\Models\Branch;
use App\Models\AcademicBand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GradeClassResource extends Resource
{
    protected static ?string $model = GradeClass::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static ?string $navigationGroup = 'إدارة المدارس';
    
    protected static ?string $modelLabel = 'الفصل الدراسي';
    protected static ?string $pluralModelLabel = 'الفصول الدراسية';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'الفصول الدراسية';
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
                Forms\Components\Section::make('بيانات الصف الدراسي')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name_ar')
                                    ->label('اسم الصف (عربي)')
                                    ->required()
                                    ->maxLength(255),
                                    
                                Forms\Components\TextInput::make('name_en')
                                    ->label('اسم الصف (إنجليزي)')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Select::make('academic_band_id')
                                    ->label('الفرقة الدراسية')
                                    ->options(AcademicBand::query()->pluck('name_ar', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('branch_id')
                                    ->label('الفرع')
                                    ->options(Branch::query()->pluck('name_ar', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\TextInput::make('grade_order')
                                    ->label('ترتيب الصف')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(12),
                            ]),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Textarea::make('description')
                                    ->label('الوصف')
                                    ->rows(3)
                                    ->columnSpan(1),
                                    
                                Forms\Components\Toggle::make('is_active')
                                    ->label('مفعل')
                                    ->default(true)
                                    ->columnSpan(1),
                            ]),
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
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('اسم الصف (عربي)')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('name_en')
                    ->label('اسم الصف (إنجليزي)')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('academicBand.name_ar')
                    ->label('الفرقة الدراسية')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grade_order')
                    ->label('ترتيب الصف')
                    ->sortable()
                    ->alignCenter(),
                    
                // Tables\Columns\TextColumn::make('students_count')
                //     ->label('عدد الطلاب')
                //     ->counts('students')
                //     ->alignCenter()
                //     ->badge()
                //     ->color('success'),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('branch.name_ar')
                    ->label('الفرع')
                    ->searchable()
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('academic_band_id')
                    ->label('المرحلة الدراسية')
                    ->options(AcademicBand::query()->pluck('name_ar', 'id'))
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueLabel('مفعل')
                    ->falseLabel('غير مفعل')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ])
            ->defaultSort('grade_order', 'asc');
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
            'index' => Pages\ListGradeClasses::route('/'),
            'create' => Pages\CreateGradeClass::route('/create'),
            'edit' => Pages\EditGradeClass::route('/{record}/edit'),
        ];
    }
}
