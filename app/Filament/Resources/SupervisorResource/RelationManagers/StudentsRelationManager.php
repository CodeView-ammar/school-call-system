<?php

namespace App\Filament\Resources\SupervisorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Student;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\DetachAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    protected static ?string $recordTitleAttribute = 'name_ar';

    protected static ?string $title = 'الطلاب المرتبطين';

    protected static ?string $modelLabel = 'طالب';

    protected static ?string $pluralModelLabel = 'الطلاب';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name_ar')
                    ->label('الاسم بالعربية')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name_ar')
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('كود الطالب')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name_ar')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Student $record): string => $record->name_en ?? ''),

                Tables\Columns\TextColumn::make('student_number')
                    ->label('رقم الطالب')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('gender')
                    ->label('الجنس')
                    ->formatStateUsing(fn (string $state): string => 
                        $state === 'male' ? 'ذكر' : 'أنثى'
                    )
                    ->badge()
                    ->color(fn (string $state): string => 
                        $state === 'male' ? 'blue' : 'pink'
                    ),

                Tables\Columns\TextColumn::make('age')
                    ->label('العمر')
                    ->state(function (Student $record): string {
                        return $record->age ? $record->age . ' سنة' : 'غير محدد';
                    }),

                Tables\Columns\TextColumn::make('pivot.assigned_date')
                    ->label('تاريخ التكليف')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pivot.notes')
                    ->label('الملاحظات')
                    ->limit(50)
                    ->placeholder('لا توجد ملاحظات'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->label('الجنس')
                    ->options([
                        'male' => 'ذكر',
                        'female' => 'أنثى',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('جميع الحالات')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('ربط طالب')
                    ->form(fn (AttachAction $action): array => [
                        Select::make('recordId')
                            ->label('الطالب')
                            ->options(function () {
                                $supervisorSchoolId = $this->ownerRecord->school_id;
                                return Student::where('school_id', $supervisorSchoolId)
                                    ->where('is_active', true)
                                    ->whereNotIn('id', $this->ownerRecord->students->pluck('id'))
                                    ->get()
                                    ->mapWithKeys(function ($student) {
                                        return [$student->id => "{$student->name_ar} ({$student->code})"];
                                    });
                            })
                            ->searchable()
                            ->required()
                            ->placeholder('اختر طالباً'),

                        Textarea::make('notes')
                            ->label('الملاحظات')
                            ->placeholder('أدخل أي ملاحظات حول هذا التكليف')
                            ->rows(3),

                        DateTimePicker::make('assigned_date')
                            ->label('تاريخ التكليف')
                            ->default(now())
                            ->required(),
                    ])
                    ->modalHeading('ربط طالب بالمساعد')
                    ->modalDescription('اختر الطالب الذي تريد ربطه بهذا المساعد')
                    ->successNotificationTitle('تم ربط الطالب بنجاح'),
            ])
            ->actions([
                Tables\Actions\Action::make('edit_assignment')
                    ->label('تعديل التكليف')
                    ->icon('heroicon-o-pencil')
                    ->form([
                        Textarea::make('notes')
                            ->label('الملاحظات')
                            ->default(fn (Student $record): string => 
                                $record->pivot->notes ?? ''
                            )
                            ->rows(3),

                        DateTimePicker::make('assigned_date')
                            ->label('تاريخ التكليف')
                            ->default(fn (Student $record): string => 
                                $record->pivot->assigned_date
                            )
                            ->required(),
                    ])
                    ->action(function (array $data, Student $record): void {
                        $this->ownerRecord->students()->updateExistingPivot($record->id, [
                            'notes' => $data['notes'],
                            'assigned_date' => $data['assigned_date'],
                        ]);
                    })
                    ->modalHeading('تعديل تكليف الطالب')
                    ->successNotificationTitle('تم تحديث التكليف بنجاح'),

                Tables\Actions\ViewAction::make()
                    ->label('عرض')
                    ->url(fn (Student $record): string => 
                        route('filament.admin.resources.students.view', $record)
                    ),

                DetachAction::make()
                    ->label('فصل')
                    ->requiresConfirmation()
                    ->modalHeading('فصل الطالب من المساعد')
                    ->modalDescription(fn (Student $record): string => 
                        "هل أنت متأكد من فصل الطالب {$record->name_ar} من هذا المساعد؟"
                    )
                    ->successNotificationTitle('تم فصل الطالب بنجاح'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('فصل المحدد')
                        ->requiresConfirmation()
                        ->modalHeading('فصل الطلاب المحددين')
                        ->modalDescription('هل أنت متأكد من فصل جميع الطلاب المحددين من هذا المساعد؟'),
                ]),
            ])
            ->defaultSort('pivot.assigned_date', 'desc');
    }
}