<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentCallResource\Pages;
use App\Models\StudentCall;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class StudentCallResource extends Resource
{
    protected static ?string $model = StudentCall::class;

    
    protected static ?string $modelLabel = 'نداءات الصباح';
    
    
    protected static ?string $navigationGroup = 'ندائات';
    
    protected static ?int $navigationSort = 0;
    
    protected static ?string $navigationIcon = 'heroicon-o-phone';
    protected static ?string $navigationLabel = 'نداءات الطلاب';
    protected static ?string $pluralModelLabel = 'نداءات الطلاب';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()?->school_id) {
            $query->where('school_id', auth()->user()->school_id);
        }

        // عرض النداءات المسائية فقط
        $query->where('call_period', \App\Models\StudentCall::PERIOD_EVENING);

        return $query;
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('school_id')
                ->label('مدرسة')
                ->relationship('school', 'name_ar')
                ->required(),
            Forms\Components\Select::make('branch_id')
                ->label('فرع')
                ->relationship('branch', 'name_ar')
                ->required(),
            Forms\Components\Select::make('student_id')
                ->label('طالب')
                ->relationship('student', 'name_ar')
                ->required(),
            Forms\Components\DateTimePicker::make('call_cdate')
                ->label('تاريخ النداء')
                ->required(),
            Forms\Components\DateTimePicker::make('call_edate')
                ->label('تاريخ الانتهاء')
                ->nullable(),
            Forms\Components\Select::make('user_id')
                ->label('المستخدم')
                ->relationship('user', 'name')
                ->required(),
            Forms\Components\Select::make('status')
                ->label('الحالة')
                ->options([
                    'prepare' => 'طلب الاستعداد',
                    'leave' => 'طلب المغادرة',
                    'with_teacher' => 'مع المعلم',
                    'to_gate' => 'في الطريق إلى البوابة',
                    'received' => 'تم استلام الطالب',
                    'canceled' => 'إلغاء',
                    'homeward' => 'في طريق العودة',
                    'arrived_home' => 'وصل إلى المنزل',
                    'delivered' => 'تم التسليم',
                    'morning_prepare'=>'استعداد',
                    'morning_arrived'=>'تم الوصول',
                    'morning_received'=>'استلام الطالب',
                    'morning_delivered'=>'تسليم للمدرسة',
                    'morning_canceled'=>'تم الإلغاء',
                ])
                ->required()
                ->native(false),
            Forms\Components\Select::make('caller_type')
                ->label('تم النداء بواسطة')
                ->options([
                    'guardian' => 'ولي الأمر',
                    'assistant' => 'المساعد',
                    'bus' => 'الباص',
                ])
                ->required()
                ->native(false),
            Forms\Components\Select::make('call_level')
                ->label('نوع النداء')
                ->options([
                    'normal' => 'نداء عادي',
                    'urgent' => 'نداء مستعجل',
                ])
                ->default('normal')
                ->required()
                ->native(false),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('school.name_ar')->label('المدرسة'),
                Tables\Columns\TextColumn::make('call_level')
                    ->label('نوع النداء')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'normal' => 'نداء عادي',
                        'urgent' => 'نداء مستعجل',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('student.name_ar')->label('اسم الطالب'),
                Tables\Columns\TextColumn::make('user.name')->label('اسم المنادي'),
                Tables\Columns\TextColumn::make('caller_type')
                    ->label('صفة المنادي')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'guardian' => 'ولي الأمر',
                        'assistant' => 'المساعد',
                        'bus' => 'الباص',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('حالة النداء')
                    ->formatStateUsing(function ($state) {
                        $labels = [
                            'prepare' => 'طلب الاستعداد',
                            'leave' => 'طلب المغادرة',
                            'with_teacher' => 'مع المعلم',
                            'to_gate' => 'في الطريق إلى البوابة',
                            'received' => 'تم استلام الطالب',
                            'canceled' => 'إلغاء',
                            'homeward' => 'في طريق العودة',
                            'arrived_home' => 'وصل إلى المنزل',
                            'delivered' => 'تم التسليم',
                            'morning_prepare'=>'استعداد',
                            'morning_arrived'=>'تم الوصول',
                            'morning_received'=>'استلام الطالب',
                            'morning_delivered'=>'تسليم للمدرسة',
                            'morning_canceled'=>'تم الإلغاء',
                        ];
                        return $labels[$state] ?? $state;
                    }),
                Tables\Columns\TextColumn::make('call_cdate')->label('تاريخ النداء')->dateTime(),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ التسجيل')->dateTime(),
            ])
            ->filters([
                //
            ])
           ->actions([
            Tables\Actions\Action::make('show')
                ->label('عرض التفاصيل')
                ->icon('heroicon-o-eye')
                ->modalHeading('تفاصيل نداء الطالب')
                ->modalButton('إغلاق')
                ->modalContent(function ($record) {
                    // تمرير بيانات العرض + إضافة زر طباعة وكود JS
                    return view('filament.admin.resources.student-calls.show-student-call', [
                        'record' => $record,
                        'printScript' => true, // علامة لإضافة سكريبت الطباعة
                    ]);
                }),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentCalls::route('/'),
            'show' => Pages\ShowStudentCall::route('/{record}'),
            // يمكنك تفعيل create/edit إذا احتجت
        ];
    }
}
