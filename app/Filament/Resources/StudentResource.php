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
    return $form->schema([
        // بيانات المدرسة والفرع
        Forms\Components\Section::make('معلومات المدرسة')
            ->schema([
                Forms\Components\Select::make('school_id')
                    ->label('المدرسة')
                    ->options(fn () => \App\Models\School::pluck('name_ar', 'id'))
                    ->default(auth()->user()?->school_id)
                    ->disabled(fn () => auth()->user()?->school_id !== null)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('branch_id', null)),

               Forms\Components\Select::make('branch_id')
                        ->label('الفرع')
                        ->options(function (callable $get) {
                            $schoolId = auth()->user()?->school_id ?? $get('school_id');
                            if (!$schoolId) return [];
                            return \App\Models\Branch::where('school_id', $schoolId)->pluck('name_ar', 'id');
                        })
                        ->required()
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set) => $set('grade_class_id', null)), // ✅ reset لما يغير الفرع

                    Forms\Components\Select::make('grade_class_id')
                        ->label('الفصل الدراسي')
                        ->options(function (callable $get) {
                            $branchId = $get('branch_id');
                            if (!$branchId) {
                                return [];
                            }
                            return \App\Models\GradeClass::where('branch_id', $branchId)
                                ->where('is_active', true)
                                ->pluck('name_ar', 'id');
                        })
                        ->required()
                        ->searchable()
                        ->reactive(),
                    ])
            ->columns(2),

        // بيانات الطالب الشخصية
        Forms\Components\Section::make('معلومات الطالب')
            ->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('name_ar')
                        ->label('اسم الطالب (عربي)')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('name_en')
                        ->label('اسم الطالب (إنجليزي)')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('student_number')
                        ->label('رقم الطالب')
                        ->unique(ignoreRecord: true)
                        ->required()
                        ->maxLength(20),
                ]),
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('national_id')
                        ->label('رقم الهوية / الإقامة')
                        ->maxLength(20),
                    Forms\Components\Select::make('gender')
                        ->label('الجنس')
                        ->options(['male' => 'ذكر', 'female' => 'أنثى'])
                        ->required(),
                    Forms\Components\DatePicker::make('birth_date')
                        ->label('تاريخ الميلاد')
                        ->maxDate(now()),
                ]),
            Forms\Components\FileUpload::make('photo')
                ->label('صورة الطالب')
                ->image()
                ->directory('student-photos')
                ->imageResizeMode('cover')
                ->imageCropAspectRatio('1:1')
                ->imageResizeTargetWidth(300)
                ->imageResizeTargetHeight(300)
                ->maxSize(2048) // بالحجم بالكيلوبايت (2MB)
                ->rules(['image', 'mimes:jpeg,jpg,png,webp'])
            ]),

        // بيانات الصف والفرقة
        Forms\Components\Section::make('المستوى الدراسي')
            ->schema([
                Forms\Components\Grid::make(2)->schema([
                    // Forms\Components\Select::make('grade_id')
                    //     ->label('الصف الدراسي')
                    //     ->options(function (callable $get) {
                    //         $schoolId = auth()->user()?->school_id ?? $get('school_id');
                    //         if (!$schoolId) return [];
                    //         return \App\Models\Grade::where('school_id', $schoolId)->pluck('name_ar', 'id');
                    //     })
                    //     ->searchable()
                    //     ->required()
                    //     ->reactive()
                    //     ->afterStateUpdated(fn ($state, callable $set) => $set('class_id', null)),

                    // Forms\Components\Select::make('class_id')
                    //     ->label('الفصل')
                    //     ->options(function (callable $get) {
                    //         $gradeId = $get('grade_id');
                    //         if (!$gradeId) return [];
                    //         return \App\Models\SchoolClass::where('grade_id', $gradeId)->pluck('name_ar', 'id');
                    //     })
                    //     ->searchable()
                    //     ->required(),
                ]),
                Forms\Components\Select::make('academic_band_id')
                    ->label('الفرقة')
                    ->options(function (callable $get) {
                        $schoolId = auth()->user()?->school_id ?? $get('school_id');
                        if (!$schoolId) return [];
                        return \App\Models\AcademicBand::where('school_id', $schoolId)->pluck('name_ar', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                // Forms\Components\DatePicker::make('enrollment_date')
                //     ->label('تاريخ التسجيل')
                //     ->default(now())
                //     ->required(),
            ]),

        // العنوان وموقع الخريطة
        Forms\Components\Section::make('معلومات السكن والطوارئ')
            ->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('phone')
                        ->label('رقم الهاتف')
                        ->tel()
                        ->maxLength(20),
                    Forms\Components\TextInput::make('emergency_contact')
                        ->label('هاتف الطوارئ')
                        ->tel()
                        ->maxLength(20),
                ]),
                Forms\Components\Textarea::make('address_ar')
                    ->label('العنوان (عربي)')
                    ->rows(2),
                Forms\Components\Textarea::make('address_en')
                    ->label('Address (English)')
                    ->rows(2),
                Forms\Components\Textarea::make('medical_notes')
                    ->label('ملاحظات طبية')
                    ->rows(2),
                    // العمود الأيسر: الخريطة
                    Forms\Components\Section::make('الخريطة')
                        ->schema([
                            Forms\Components\ViewField::make('map')
                                ->label('حدد الموقع على الخريطة')
                                ->view('filament.custom.map-picker')
                                ->extraAttributes(['wire:ignore']),
                        ]),

                Forms\Components\TextInput::make('latitude')
                    ->label('خط العرض')
                    ->disabled(),

                Forms\Components\TextInput::make('longitude')
                    ->label('خط الطول')
                    ->disabled(),
                // Forms\Components\TextInput::make('latitude')
                //     ->hidden()
                //     ->dehydrated(),
                // Forms\Components\TextInput::make('longitude')
                //     ->hidden()
                //     ->dehydrated(),
            ]),

        // بيانات النقل وتفعيل الحساب
        Forms\Components\Section::make('معلومات النقل')
            ->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Select::make('bus_id')
                            ->label('الحافلة')
                            ->searchable()
                            ->reactive()
                            ->options(function (callable $get) {
                                $branchId = $get('branch_id');
                                if (!$branchId) return [];
                                return \App\Models\Bus::where('branch_id', $branchId)
                                    ->pluck('number', 'id'); // ✅ هنا المفتاح id والقيمة number
                            }),

                    Forms\Components\TextInput::make('pickup_location')
                        ->label('نقطة الاستلام')
                        ->maxLength(255),
                ]),
            ]),

        Forms\Components\Toggle::make('is_active')
            ->label('نشط')
            ->default(true),
    ])->columns(2);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('اسم الطالب')
                    ->searchable()
                    ->sortable(),
            Tables\Columns\TextColumn::make('school.name_ar')
                ->label('المدرسة')
                ->searchable()
                ->sortable()
                ->visible(fn () => auth()->user()?->school_id === null),

            Tables\Columns\TextColumn::make('branch.name_ar')
                ->label('الفرع')
                ->searchable()
                ->sortable(),
                Tables\Columns\TextColumn::make('gradeClass.name_ar')
                    ->label('الفصل الدراسي')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('photo')
                    ->label('الصورة')
                    ->circular(),
                Tables\Columns\TextColumn::make('student_number')
                    ->label('رقم الطالب')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch.name_ar')
                    ->label('الفرع')
                    ->sortable(),
                Tables\Columns\TextColumn::make('academicBand.name_ar')
            ->label('الفرقة')
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
                Tables\Filters\SelectFilter::make('academicBand')
                    ->label('الفرقة')
                    ->relationship('academicBand', 'name_ar'),
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
            'view' => Pages\ViewStudent::route('/{record}'),
        ];
    }
}
