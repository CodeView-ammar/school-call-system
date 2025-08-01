<?php
namespace App\Filament\Resources;

use App\Filament\Resources\AcademicBandResource\Pages;
use App\Models\AcademicBand;
use App\Models\School;
use App\Models\Gate;

use App\Models\EducationLevel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables;
use Filament\Tables\Table;

class AcademicBandResource extends Resource
{
    protected static ?string $model = AcademicBand::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static ?string $navigationGroup = 'إدارة المدارس';
    
    protected static ?string $modelLabel = 'الفرقة الدراسية';

    protected static ?string $pluralModelLabel = 'الفرق الدراسية';
    protected static ?int $navigationSort = 3;

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

                 Forms\Components\Select::make('school_id')
                    ->label('المدرسة')
                    ->options(School::pluck('name_ar', 'id'))
                    ->default(auth()->user()?->school_id)
                    ->disabled(fn () => auth()->user()?->school_id !== null)
                    ->hidden(fn () => auth()->user()?->school_id !== null)
                    ->required()
                    ->reactive(), 
                    
                    Forms\Components\Select::make('gate_id')
                    ->label('البوابة')
                    ->options(function (callable $get) {
                        $schoolId = $get('school_id');
                        return $schoolId
                            ? Gate::where('school_id', $schoolId)->pluck('name', 'id')
                            : [];
                    })
                    ->searchable()
                    ->required()
                    ->disabled(fn (callable $get) => $get('school_id') === null),
                    Forms\Components\Select::make('education_level_id')
                        ->label('المرحلة الدراسية')
                        ->relationship('educationLevel', 'name_ar')
                        ->required(),
                Forms\Components\TextInput::make('name_ar')
                    ->label('الاسم العربي')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('name_en')
                    ->label('الاسم الإنجليزي')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('short_name')
                    ->label('الاسم المختصر')
                    ->required()
                    ->maxLength(50),

                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('اسم المرحلة (عربي)')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('name_en')
                    ->label('اسم المرحلة (إنجليزي)')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('educationLevel.name_ar')
                    ->label('المرحلة الدراسية')
                    ->searchable()
                    ->sortable(),
                    
                    
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
                Tables\Filters\SelectFilter::make('education_level_id')
                    ->label('المرحلة الدراسية')
                    ->options(EducationLevel::query()->pluck('name_ar', 'id'))
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
            ]);
            // ->defaultSort('grade_order', 'asc');
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAcademicBands::route('/'),
            // 'create' => Pages\CreateAcademicBand::route('/create'),
            // 'edit' => Pages\EditAcademicBand::route('/{record}/edit'),
        ];
    }
}

