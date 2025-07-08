<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Student;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'الطلاب';
    
    protected static ?string $modelLabel = 'طالب';
    
    protected static ?string $pluralModelLabel = 'الطلاب';
    
    protected static ?string $navigationGroup = 'إدارة الطلاب';
    
    protected static ?int $navigationSort = 1;

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
                Forms\Components\Section::make('معلومات الطالب الأساسية')
                    ->schema([
                      
                        Forms\Components\Select::make('school_id')
                            ->label('المدرسة')
                            ->options(fn () => \App\Models\School::pluck('name_ar', 'id'))
                            ->default(auth()->user()?->school_id)
                            ->disabled(fn () => auth()->user()?->school_id !== null)
                            ->required()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('branch_id', null))
                            ->reactive(),
                            Forms\Components\Select::make('branch_id')
                                ->label('الفرع')
                                ->options(function (callable $get) {
                                    $schoolId = auth()->user()?->school_id ?? $get('school_id');
                                    if (!$schoolId) {
                                        return [];
                                    }
                                    return Branch::where('school_id', $schoolId)->pluck('name_ar', 'id');
                                })
                                ->required()
                                ->searchable()
                                ->preload(),
                        Forms\Components\Grid::make(2)
                            ->schema([
                              Forms\Components\Select::make('school_class_id')
                                    ->label('الصف')
                                    ->options(function (callable $get) {
                                        $schoolId = auth()->user()?->school_id ?? $get('school_id');
                                        if (!$schoolId) {
                                            return [];
                                        }

                                        return \App\Models\GradeClass::where('school_id', $schoolId)
                                            ->pluck('name_ar', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->required()
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('section_id', null))
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name_ar')
                                    ->label('اسم الطالب (عربي)')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('name_en')
                                    ->label('Student Name (English)')
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('student_number')
                                    ->label('رقم الطالب')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('national_id')
                                    ->label('رقم الهوية/الإقامة')
                                    ->maxLength(20),
                                Forms\Components\Select::make('gender')
                                    ->label('الجنس')
                                    ->options([
                                        'male' => 'ذكر',
                                        'female' => 'أنثى',
                                    ])
                                    ->required(),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('birth_date')
                                    ->label('تاريخ الميلاد')
                                    ->maxDate(now()),
                                Forms\Components\FileUpload::make('photo')
                                    ->label('صورة الطالب')
                                    ->image()
                                    ->directory('student-photos')
                                    ->imageResizeMode('cover')
                                    ->imageCropAspectRatio('1:1')
                                    ->imageResizeTargetWidth('300')
                                    ->imageResizeTargetHeight('300'),
                            ]),
                    ]),
                    
                Forms\Components\Section::make('معلومات السكن والطوارئ')
                    ->schema([
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Textarea::make('address_ar')
                                    ->label('العنوان (عربي)')
                                    ->rows(2),
                                Forms\Components\Textarea::make('address_en')
                                    ->label('Address (English)')
                                    ->rows(2),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('emergency_contact')
                                    ->label('هاتف الطوارئ')
                                    ->tel(),
                                Forms\Components\Textarea::make('medical_notes')
                                    ->label('ملاحظات طبية')
                                    ->rows(2),
                            ]),
                    ]),
                    
                Forms\Components\Section::make('معلومات النقل')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('bus_id')
                                    ->label('الحافلة')
                                    ->relationship('bus', 'number')
                                    ->searchable(),
                                Forms\Components\TextInput::make('pickup_location')
                                    ->label('نقطة الاستلام')
                                    ->maxLength(255),
                            ]),
                    ]),
                    
                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
                Forms\Components\View::make('student-map-picker')
                    ->view('filament.forms.components.student-map-picker')
                    ->afterStateUpdated(function ($state, callable $set) {
                    if (isset($state['lat']) && isset($state['lng'])) {
                    $set('latitude', $state['lat']);
                    $set('longitude', $state['lng']);
                }
                }),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('الصورة')
                    ->circular(),
                Tables\Columns\TextColumn::make('student_number')
                    ->label('رقم الطالب')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('اسم الطالب')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch.name_ar')
                    ->label('الفرع')
                    ->sortable(),
                Tables\Columns\TextColumn::make('schoolClass.name_ar')
                    ->label('الصف')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label('الجنس')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => 'ذكر',
                        'female' => 'أنثى',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('bus.number')
                    ->label('الحافلة')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch')
                    ->label('الفرع')
                    ->relationship('branch', 'name_ar'),
                Tables\Filters\SelectFilter::make('schoolClass')
                    ->label('الصف')
                    ->relationship('schoolClass', 'name_ar'),
                Tables\Filters\SelectFilter::make('gender')
                    ->label('الجنس')
                    ->options([
                        'male' => 'ذكر',
                        'female' => 'أنثى',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('عرض'),
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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
