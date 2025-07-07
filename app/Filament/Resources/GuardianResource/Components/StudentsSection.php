<?php

namespace App\Filament\Resources\GuardianResource\Components;

use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Components\Component;

class StudentsSection
{
    public static function make(): Component
    {
        return Forms\Components\Section::make('إدارة الطلاب المرتبطين')
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
                                    ->active()
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
                                    ->active()
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
                    ->minItems(1)
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
            ->collapsed(false);
    }
}