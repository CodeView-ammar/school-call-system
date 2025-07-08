<?php

namespace App\Filament\Resources\GuardianResource\RelationManagers;

use App\Models\Student;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    protected static ?string $title = 'الأطفال المرتبطين';
    
    protected static ?string $recordTitleAttribute = 'name_ar';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            
            Forms\Components\Grid::make(2)
                ->schema([
                    //         auth()->user()?->school_id === null
                    // ? Forms\Components\Select::make('school_id')
                    //     ->label('المدرسة')
                    //     ->relationship('school', 'name_ar') // تأكد من أن العلاقة صحيحة
                    //     ->required()
                    //     ->searchable()
                    //     ->preload(),
                    Forms\Components\TextInput::make('name_ar')
                        ->label('الاسم (عربي)')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('name_en')
                        ->label('الاسم (إنجليزي)')
                        ->maxLength(255),
                ]),
            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->label('كود الطالب')
                        ->required()
                        ->unique('students', 'code', ignoreRecord: true)
                        ->maxLength(50),
                    Forms\Components\TextInput::make('student_number')
                        ->label('رقم الطالب')
                        ->unique('students', 'student_number', ignoreRecord: true)
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
                ->unique('students', 'national_id', ignoreRecord: true)
                ->maxLength(20),
            Forms\Components\Toggle::make('is_active')
                ->label('نشط')
                ->default(true),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('كود الطالب')
                    ->searchable(),
                Tables\Columns\TextColumn::make('student_number')
                    ->label('رقم الطالب')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label('الجنس')
                    ->formatStateUsing(fn (string $state): string => $state === 'male' ? 'ذكر' : 'أنثى'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                Tables\Columns\TextColumn::make('pivot.is_primary')
                    ->label('ولي أمر رئيسي')
                    ->formatStateUsing(fn ($state): string => $state ? 'نعم' : 'لا')
                    ->badge()
                    ->color(fn ($state): string => $state ? 'success' : 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->label('الجنس')
                    ->options([
                        'male' => 'ذكر',
                        'female' => 'أنثى',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('نشط'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('ربط طالب موجود')
                    ->recordSelectSearchColumns(['name_ar', 'code', 'student_number'])
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->searchable(['name_ar', 'code', 'student_number'])
                            ->getOptionLabelUsing(fn ($value): ?string => 
                                Student::find($value)?->name_ar . ' - كود: ' . Student::find($value)?->code
                            ),
                        Forms\Components\Toggle::make('is_primary')
                            ->label('ولي أمر رئيسي')
                            ->default(false),
                    ]),
                Tables\Actions\CreateAction::make()
                    ->label('إضافة طالب جديد'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DetachAction::make()->label('إلغاء الربط'),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()->label('إلغاء ربط المحدد'),
            ]);
    }
}