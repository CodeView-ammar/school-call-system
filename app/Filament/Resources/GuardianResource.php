<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuardianResource\Pages;
use App\Filament\Resources\GuardianResource\RelationManagers;
use App\Filament\Resources\GuardianResource\Actions;
use App\Models\Guardian;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GuardianResource extends Resource
{
    protected static ?string $model = Guardian::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'أولياء الأمور';

    protected static ?string $modelLabel = 'ولي أمر';

    protected static ?string $pluralModelLabel = 'أولياء الأمور';

    protected static ?string $navigationGroup = 'إدارة الطلاب';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('المعلومات الأساسية')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name_ar')
                                    ->label('الاسم (عربي)')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('name_en')
                                    ->label('Name (English)')
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('phone')
                                    ->label('رقم الهاتف')
                                    ->tel()
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                Forms\Components\TextInput::make('email')
                                    ->label('البريد الإلكتروني')
                                    ->email(),
                                Forms\Components\TextInput::make('national_id')
                                    ->label('رقم الهوية')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(20),
                            ]),
                        Forms\Components\Select::make('relationship')
                            ->label('صلة القرابة')
                            ->options([
                                'أب' => 'أب',
                                'أم' => 'أم',
                                'جد' => 'جد',
                                'جدة' => 'جدة',
                                'عم' => 'عم',
                                'خال' => 'خال',
                                'عمة' => 'عمة',
                                'خالة' => 'خالة',
                                'أخرى' => 'أخرى',
                            ])
                            ->required(),
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Textarea::make('address_ar')
                                    ->label('العنوان (عربي)')
                                    ->rows(2),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('job')
                                    ->label('المهنة')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('workplace')
                                    ->label('مكان العمل')
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('الأطفال المرتبطين')
                    ->description('يمكنك إضافة أو ربط عدة طلاب بولي الأمر')
                    ->schema([
                        Forms\Components\Repeater::make('student_relationships')
                            ->label('الطلاب')
                            ->relationship('students')
                            ->schema([
                                Forms\Components\Select::make('student_id')
                                    ->label('اختر الطالب')
                                    ->searchable()
                                    ->options(function () {
                                        return Student::query()
                                            ->where('is_active', true)
                                            ->get()
                                            ->pluck('name_ar', 'id')
                                            ->map(function ($name, $id) {
                                                $student = Student::find($id);
                                                return "{$name} - كود: {$student->code}";
                                            })
                                            ->toArray();
                                    })
                                    ->getSearchResultsUsing(function (string $search) {
                                        return Student::where('name_ar', 'like', "%{$search}%")
                                            ->orWhere('name_en', 'like', "%{$search}%")
                                            ->orWhere('code', 'like', "%{$search}%")
                                            ->orWhere('student_number', 'like', "%{$search}%")
                                            ->where('is_active', true)
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(function ($student) {
                                                return [$student->id => "{$student->name_ar} - كود: {$student->code}"];
                                            })
                                            ->toArray();
                                    })
                                    ->createOptionForm([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('name_ar')
                                                    ->label('اسم الطالب (عربي)')
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('name_en')
                                                    ->label('اسم الطالب (إنجليزي)')
                                                    ->maxLength(255),
                                            ]),
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('code')
                                                    ->label('كود الطالب')
                                                    ->required()
                                                    ->unique('students', 'code')
                                                    ->maxLength(50),
                                                Forms\Components\TextInput::make('student_number')
                                                    ->label('رقم الطالب')
                                                    ->unique('students', 'student_number')
                                                    ->maxLength(50),
                                                Forms\Components\Select::make('gender')
                                                    ->label('الجنس')
                                                    ->options([
                                                        'male' => 'ذكر',
                                                        'female' => 'أنثى',
                                                    ])
                                                    ->required(),
                                            ]),
                                        Forms\Components\DatePicker::make('date_of_birth')
                                            ->label('تاريخ الميلاد')
                                            ->required(),
                                        Forms\Components\TextInput::make('national_id')
                                            ->label('رقم الهوية')
                                            ->unique('students', 'national_id')
                                            ->maxLength(20),
                                        Forms\Components\Toggle::make('is_active')
                                            ->label('نشط')
                                            ->default(true),
                                    ])
                                    ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                        return $action
                                            ->modalHeading('إضافة طالب جديد')
                                            ->modalSubmitActionLabel('إضافة')
                                            ->modalCancelActionLabel('إلغاء');
                                    })
                                    ->required()
                                    ->distinct(),
                                
                                Forms\Components\Toggle::make('is_primary')
                                    ->label('ولي أمر رئيسي')
                                    ->default(true)
                                    ->helperText('هل هذا الولي هو المسؤول الرئيسي عن هذا الطالب؟'),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->cloneable()
                            ->minItems(0)
                            ->maxItems(10)
                            ->addActionLabel('إضافة طالب آخر')
                            ->deleteActionLabel('إزالة')
                            ->reorderableWithButtons()
                            ->itemLabel(function (array $state): ?string {
                                if (!isset($state['student_id'])) {
                                    return 'طالب جديد';
                                }
                                
                                $student = Student::find($state['student_id']);
                                if (!$student) {
                                    return 'طالب غير موجود';
                                }
                                
                                $primaryText = ($state['is_primary'] ?? false) ? ' (رئيسي)' : '';
                                return "{$student->name_ar} - كود: {$student->code}{$primaryText}";
                            }),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('رقم الهاتف')
                    ->searchable(),
                Tables\Columns\TextColumn::make('relationship')
                    ->label('صلة القرابة')
                    ->badge(),
                Tables\Columns\TextColumn::make('students_count')
                    ->label('عدد الأطفال')
                    ->counts('students')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('relationship')
                    ->label('صلة القرابة')
                    ->options([
                        'أب' => 'أب',
                        'أم' => 'أم',
                        'جد' => 'جد',
                        'جدة' => 'جدة',
                        'عم' => 'عم',
                        'خال' => 'خال',
                        'عمة' => 'عمة',
                        'خالة' => 'خالة',
                        'أخرى' => 'أخرى',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('نشط'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Actions\BulkAssignStudentsAction::make(),
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StudentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuardians::route('/'),
            'create' => Pages\CreateGuardian::route('/create'),
            'edit' => Pages\EditGuardian::route('/{record}/edit'),
        ];
    }
}