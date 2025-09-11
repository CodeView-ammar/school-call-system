<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AcademicBandWeekDayResource\Pages;
use App\Models\AcademicBandWeekDay;
use App\Models\School;
use App\Models\AcademicBand;
use App\Models\WeekDay;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Filament\Notifications\Notification;

class AcademicBandWeekDayResource extends Resource
{
    protected static ?string $model = AcademicBandWeekDay::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'إدارة المدارس';

    protected static ?string $modelLabel = 'جدولة الفرق الدراسية';

    protected static ?string $pluralModelLabel = 'جدولة الفرق الدراسية';

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
                Forms\Components\Select::make('school_id')
                    ->label('المدرسة')
                    ->relationship('school', 'name_ar')
                    ->required()
                    ->reactive()
                    ->default(fn () => auth()->user()?->school_id)
                    ->disabled(fn () => auth()->user()?->school_id !== null)
                    ->dehydrated(true),

                Forms\Components\Select::make('academic_band_id')
                    ->label('الفرقة الدراسية')
                    ->options(function (callable $get) {
                        $schoolId = $get('school_id') ?? auth()->user()?->school_id;
                        if (!$schoolId) return [];

                        return AcademicBand::where('school_id', $schoolId)
                            ->where('is_active', true)
                            ->pluck('name_ar', 'id');
                    })
                    ->required()
                    ->reactive()
                    ->searchable()
                    ->afterStateUpdated(function (callable $set) {
                        // مسح اختيار اليوم عند تغيير الفرقة
                        $set('week_day_id', null);
                    }),

                Forms\Components\Select::make('week_day_id')
                    ->label('يوم الأسبوع')
                    ->options(function (callable $get) {
                        $schoolId = $get('school_id') ?? auth()->user()?->school_id;
                        if (!$schoolId) return [];

                        return WeekDay::where('school_id', $schoolId)
                            ->pluck('day', 'day_id');
                    })
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                // الحصول على البيانات من الطلب
                                $data = request()->all();
                                $schoolId = $data['school_id'] ?? auth()->user()?->school_id;
                                $academicBandId = $data['academic_band_id'] ?? null;
                                $recordId = request()->route('record');

                                if (!$academicBandId || !$value) {
                                    return;
                                }

                                // التحقق من وجود سجل مكرر
                                $query = AcademicBandWeekDay::where('school_id', $schoolId)
                                    ->where('academic_band_id', $academicBandId)
                                    ->where('week_day_id', $value);

                                // استثناء السجل الحالي عند التعديل
                                if ($recordId) {
                                    $query->where('id', '!=', $recordId);
                                }

                                if ($query->exists()) {
                                    $fail('هذا اليوم مسجل مسبقاً لهذه الفرقة الدراسية.');
                                    
                                    Notification::make()
                                        ->title('خطأ في الإدخال')
                                        ->body('هذا اليوم مسجل مسبقاً لهذه الفرقة الدراسية. يرجى اختيار يوم آخر.')
                                        ->danger()
                                        ->send();
                                }
                            };
                        },
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TimePicker::make('start_time')
                            ->label('وقت البداية')
                            ->seconds(false)
                            ->required()
                            ->reactive(),

                        Forms\Components\TimePicker::make('end_time')
                            ->label('وقت النهاية')
                            ->seconds(false)
                            ->required()
                            ->after('start_time')
                            ->reactive()
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        $data = request()->all();
                                        $startTime = $data['start_time'] ?? null;
                                        
                                        if (!$startTime || !$value) {
                                            return;
                                        }

                                        // التحقق من أن وقت النهاية بعد وقت البداية
                                        if (strtotime($value) <= strtotime($startTime)) {
                                            $fail('وقت النهاية يجب أن يكون بعد وقت البداية.');
                                            
                                            Notification::make()
                                                ->title('خطأ في الوقت')
                                                ->body('وقت النهاية يجب أن يكون بعد وقت البداية.')
                                                ->danger()
                                                ->send();
                                        }

                                        // التحقق من تداخل الأوقات (اختياري)
                                        $schoolId = $data['school_id'] ?? auth()->user()?->school_id;
                                        $academicBandId = $data['academic_band_id'] ?? null;
                                        $weekDayId = $data['week_day_id'] ?? null;
                                        $recordId = request()->route('record');

                                        if ($academicBandId && $weekDayId) {
                                            $conflictQuery = AcademicBandWeekDay::where('school_id', $schoolId)
                                                ->where('academic_band_id', $academicBandId)
                                                ->where('week_day_id', $weekDayId)
                                                ->where('is_active', true)
                                                ->where(function($q) use ($startTime, $value) {
                                                    $q->where(function($q2) use ($startTime, $value) {
                                                        // التحقق من التداخل
                                                        $q2->whereBetween('start_time', [$startTime, $value])
                                                           ->orWhereBetween('end_time', [$startTime, $value])
                                                           ->orWhere(function($q3) use ($startTime, $value) {
                                                               $q3->where('start_time', '<=', $startTime)
                                                                  ->where('end_time', '>=', $value);
                                                           });
                                                    });
                                                });

                                            if ($recordId) {
                                                $conflictQuery->where('id', '!=', $recordId);
                                            }

                                            if ($conflictQuery->exists()) {
                                                $fail('يوجد تداخل في الأوقات مع جدول آخر لنفس الفرقة في نفس اليوم.');
                                                
                                                Notification::make()
                                                    ->title('تداخل في الأوقات')
                                                    ->body('يوجد تداخل في الأوقات مع جدول آخر لنفس الفرقة في نفس اليوم.')
                                                    ->warning()
                                                    ->send();
                                            }
                                        }
                                    };
                                },
                            ]),
                    ]),

                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true)
                    ->helperText('تفعيل أو إلغاء تفعيل هذا الجدول'),

                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3)
                    ->placeholder('أي ملاحظات إضافية حول هذا الجدول...')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('school.name_ar')
                    ->label('المدرسة')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()?->school_id === null),

                Tables\Columns\TextColumn::make('academicBand.name_ar')
                    ->label('الفرقة الدراسية')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('weekDay.day')
                    ->label('يوم الأسبوع')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'السبت' => 'info',
                        'الأحد' => 'success',
                        'الإثنين' => 'warning',
                        'الثلاثاء' => 'primary',
                        'الأربعاء' => 'danger',
                        'الخميس' => 'gray',
                        'الجمعة' => 'secondary',
                        default => 'primary',
                    }),

                Tables\Columns\TextColumn::make('time_range')
                    ->label('الوقت')
                    ->getStateUsing(fn ($record) => 
                        $record->start_time->format('H:i') . ' - ' . $record->end_time->format('H:i')
                    )
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-clock'),

                Tables\Columns\TextColumn::make('duration')
                    ->label('المدة')
                    ->getStateUsing(function ($record) {
                        $start = \Carbon\Carbon::parse($record->start_time);
                        $end = \Carbon\Carbon::parse($record->end_time);
                        $diff = $end->diff($start);
                        
                        $hours = $diff->h;
                        $minutes = $diff->i;
                        
                        if ($hours > 0 && $minutes > 0) {
                            return "{$hours} ساعة و {$minutes} دقيقة";
                        } elseif ($hours > 0) {
                            return "{$hours} ساعة";
                        } else {
                            return "{$minutes} دقيقة";
                        }
                    })
                    ->badge()
                    ->color('warning'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->notes)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('school_id')
                    ->label('المدرسة')
                    ->relationship('school', 'name_ar')
                    ->visible(fn () => auth()->user()?->school_id === null),

                Tables\Filters\SelectFilter::make('academic_band_id')
                    ->label('الفرقة الدراسية')
                    ->relationship('academicBand', 'name_ar')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('week_day_id')
                    ->label('يوم الأسبوع')
                    ->options(function () {
                        $schoolId = auth()->user()?->school_id;
                        if (!$schoolId) {
                            return WeekDay::pluck('day', 'day_id');
                        }
                        return WeekDay::where('school_id', $schoolId)
                            ->pluck('day', 'day_id');
                    }),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                    
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                    
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->requiresConfirmation()
                    ->modalHeading('حذف الجدول')
                    ->modalDescription('هل أنت متأكد من حذف هذا الجدول؟ لا يمكن التراجع عن هذا الإجراء.')
                    ->modalSubmitActionLabel('نعم، احذف'),
                    
               
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->requiresConfirmation()
                        ->modalHeading('حذف الجداول المحددة')
                        ->modalDescription('هل أنت متأكد من حذف الجداول المحددة؟ لا يمكن التراجع عن هذا الإجراء.')
                        ->modalSubmitActionLabel('نعم، احذف الكل'),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('تفعيل المحدد')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('تفعيل الجداول')
                        ->modalDescription('سيتم تفعيل جميع الجداول المحددة.')
                        ->modalSubmitActionLabel('تفعيل')
                        ->action(function ($records) {
                            $count = $records->count();
                            $records->each(fn ($record) => $record->update(['is_active' => true]));
                            
                            Notification::make()
                                ->title('تم التفعيل')
                                ->body("تم تفعيل {$count} جدول بنجاح.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('إلغاء تفعيل المحدد')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('إلغاء تفعيل الجداول')
                        ->modalDescription('سيتم إلغاء تفعيل جميع الجداول المحددة.')
                        ->modalSubmitActionLabel('إلغاء التفعيل')
                        ->action(function ($records) {
                            $count = $records->count();
                            $records->each(fn ($record) => $record->update(['is_active' => false]));
                            
                            Notification::make()
                                ->title('تم إلغاء التفعيل')
                                ->body("تم إلغاء تفعيل {$count} جدول بنجاح.")
                                ->warning()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('60s'); // تحديث تلقائي كل دقيقة
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
            'index' => Pages\ListAcademicBandWeekDays::route('/'),
            'create' => Pages\CreateAcademicBandWeekDay::route('/create'),
            'edit' => Pages\EditAcademicBandWeekDay::route('/{record}/edit'),
            'view' => Pages\ViewAcademicBandWeekDay::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::when(
            auth()->user()?->school_id,
            fn ($query) => $query->where('school_id', auth()->user()->school_id)
        )->where('is_active', true)->count();

        return $count > 0 ? $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}