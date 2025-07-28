<?php

namespace App\Filament\Resources\SupervisorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Guardian;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\DetachAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;

class GuardiansRelationManager extends RelationManager
{
    protected static string $relationship = 'guardians';

    protected static ?string $recordTitleAttribute = 'name_ar';

    protected static ?string $title = 'أولياء الأمور المرتبطين';

    protected static ?string $modelLabel = 'ولي أمر';

    protected static ?string $pluralModelLabel = 'أولياء الأمور';

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
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Guardian $record): string => $record->name_en ?? ''),

                Tables\Columns\TextColumn::make('phone')
                    ->label('رقم الهاتف')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->copyable()
                    ->placeholder('غير محدد'),

                Tables\Columns\TextColumn::make('relationship')
                    ->label('صلة القرابة')
                    ->formatStateUsing(function (string $state): string {
                        return match($state) {
                            'father' => 'والد',
                            'mother' => 'والدة',
                            'grandfather' => 'جد',
                            'grandmother' => 'جدة',
                            'uncle' => 'عم/خال',
                            'aunt' => 'عمة/خالة',
                            'other' => 'أخرى',
                            default => $state,
                        };
                    })
                    ->badge()
                    ->color(function (string $state): string {
                        return match($state) {
                            'father', 'mother' => 'success',
                            'grandfather', 'grandmother' => 'info',
                            'uncle', 'aunt' => 'warning',
                            default => 'gray',
                        };
                    }),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('عدد الطلاب')
                    ->counts('students')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('pivot.assigned_date')
                    ->label('تاريخ التكليف')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\IconColumn::make('pivot.is_primary')
                    ->label('مساعد أساسي')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-outline-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),

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
                Tables\Filters\SelectFilter::make('relationship')
                    ->label('صلة القرابة')
                    ->options([
                        'father' => 'والد',
                        'mother' => 'والدة',
                        'grandfather' => 'جد',
                        'grandmother' => 'جدة',
                        'uncle' => 'عم/خال',
                        'aunt' => 'عمة/خالة',
                        'other' => 'أخرى',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('جميع الحالات')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('ربط ولي أمر')
                    ->form(fn (AttachAction $action): array => [
                        Select::make('recordId')
                            ->label('ولي الأمر')
                            ->options(function () {
                                $supervisorSchoolId = $this->ownerRecord->school_id;
                                return Guardian::where('school_id', $supervisorSchoolId)
                                    ->where('is_active', true)
                                    ->whereNotIn('id', $this->ownerRecord->guardians->pluck('id'))
                                    ->get()
                                    ->mapWithKeys(function ($guardian) {
                                        $relationship = match($guardian->relationship) {
                                            'father' => 'والد',
                                            'mother' => 'والدة',
                                            'grandfather' => 'جد',
                                            'grandmother' => 'جدة',
                                            'uncle' => 'عم/خال',
                                            'aunt' => 'عمة/خالة',
                                            'other' => 'أخرى',
                                            default => $guardian->relationship,
                                        };
                                        return [$guardian->id => "{$guardian->name_ar} ({$relationship}) - {$guardian->phone}"];
                                    });
                            })
                            ->searchable()
                            ->required()
                            ->placeholder('اختر ولي أمر'),

                        Textarea::make('notes')
                            ->label('الملاحظات')
                            ->placeholder('أدخل أي ملاحظات حول هذا التكليف')
                            ->rows(3),

                        DateTimePicker::make('assigned_date')
                            ->label('تاريخ التكليف')
                            ->default(now())
                            ->required(),

                        Forms\Components\Toggle::make('is_primary')
                            ->label('مساعد أساسي')
                            ->helperText('إذا تم تفعيله، سيصبح هذا المساعد هو المساعد الأساسي لولي الأمر'),
                    ])
                    ->modalHeading('ربط ولي أمر بالمساعد')
                    ->modalDescription('اختر ولي الأمر الذي تريد ربطه بهذا المساعد')
                    ->successNotificationTitle('تم ربط ولي الأمر بنجاح'),
            ])
            ->actions([
                Tables\Actions\Action::make('edit_assignment')
                    ->label('تعديل التكليف')
                    ->icon('heroicon-o-pencil')
                    ->form([
                        Textarea::make('notes')
                            ->label('الملاحظات')
                            ->default(fn (Guardian $record): string => 
                                $record->pivot->notes ?? ''
                            )
                            ->rows(3),

                        DateTimePicker::make('assigned_date')
                            ->label('تاريخ التكليف')
                            ->default(fn (Guardian $record): string => 
                                $record->pivot->assigned_date
                            )
                            ->required(),

                        Forms\Components\Toggle::make('is_primary')
                            ->label('مساعد أساسي')
                            ->default(fn (Guardian $record): bool => 
                                $record->pivot->is_primary ?? false
                            )
                            ->helperText('إذا تم تفعيله، سيصبح هذا المساعد هو المساعد الأساسي لولي الأمر'),
                    ])
                    ->action(function (array $data, Guardian $record): void {
                        // إذا تم تفعيل المساعد كأساسي، قم بإلغاء تفعيل المساعدين الأساسيين الآخرين
                        if ($data['is_primary'] ?? false) {
                            $record->supervisors()->updateExistingPivot(
                                $record->supervisors->pluck('id')->toArray(),
                                ['is_primary' => false]
                            );
                        }

                        $this->ownerRecord->guardians()->updateExistingPivot($record->id, [
                            'notes' => $data['notes'],
                            'assigned_date' => $data['assigned_date'],
                            'is_primary' => $data['is_primary'] ?? false,
                        ]);
                    })
                    ->modalHeading('تعديل تكليف ولي الأمر')
                    ->successNotificationTitle('تم تحديث التكليف بنجاح'),

                Tables\Actions\ViewAction::make()
                    ->label('عرض')
                    ->url(fn (Guardian $record): string => 
                        route('filament.admin.resources.guardians.view', $record)
                    ),

                DetachAction::make()
                    ->label('فصل')
                    ->requiresConfirmation()
                    ->modalHeading('فصل ولي الأمر من المساعد')
                    ->modalDescription(fn (Guardian $record): string => 
                        "هل أنت متأكد من فصل ولي الأمر {$record->name_ar} من هذا المساعد؟"
                    )
                    ->successNotificationTitle('تم فصل ولي الأمر بنجاح'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('فصل المحدد')
                        ->requiresConfirmation()
                        ->modalHeading('فصل أولياء الأمور المحددين')
                        ->modalDescription('هل أنت متأكد من فصل جميع أولياء الأمور المحددين من هذا المساعد؟'),
                ]),
            ]);

    }
}