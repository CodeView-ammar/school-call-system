<?php

namespace App\Filament\Resources\SystemSettingResource\Pages;

use App\Filament\Resources\SystemSettingResource;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use App\Models\SystemSetting;
use App\Models\WeekDay;
use App\Models\CallType;

class SystemSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = SystemSettingResource::class;
    protected static string $view = 'filament.resources.system-setting-resource.pages.system-settings';
    
    public function getTitle(): string
    {
        $schoolName = auth()->user()->school->name_ar ?? 'النظام';
        return "إعدادات النظام - {$schoolName}";
    }

    public ?array $data = [];
    public bool $saving = false;

    public function mount(): void
    {
        $this->form->fill($this->getFormData());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('system_settings')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('إعدادات الندائات')
                            ->schema([
                                Forms\Components\Section::make('أوقات الندائات')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TimePicker::make('sys_earlycall')
                                                    ->label('وقت النداء المبكرة')
                                                    ->default('07:00')
                                                    ->required(),
                                                Forms\Components\TimePicker::make('sys_return_call')
                                                    ->label('وقت نداءالعودة')
                                                    ->default('15:00')
                                                    ->required(),
                                            ]),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TimePicker::make('sys_earlyexit')
                                                    ->label('وقت الخروج المبكر')
                                                    ->default('14:00')
                                                    ->required(),
                                                Forms\Components\TimePicker::make('sys_exit_togat')
                                                    ->label('وقت خروج الطوارئ')
                                                    ->default('12:00')
                                                    ->required(),
                                            ]),
                                    ]),
                                    
                                Forms\Components\Section::make('أنواع الندائات النشطة')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('active_call_types')
                                            ->label('أنواع الندائات المفعلة')
                                            ->options(function () {
                                                return CallType::pluck('ctype_name_ar', 'ctype_id')->toArray();
                                            })
                                            ->columns(2)
                                            ->gridDirection('row'),
                                    ]),
                            ]),
                            
                        Forms\Components\Tabs\Tab::make('أيام العمل')
                            ->schema([
                                Forms\Components\Section::make('جدول أيام العمل')
                                    ->schema([
                                        Forms\Components\Repeater::make('week_days')
                                            ->label('أيام الأسبوع')
                                            ->schema([
                                                Forms\Components\Grid::make(4)
                                                    ->schema([
                                                        Forms\Components\Select::make('day_name')
                                                            ->label('اليوم')
                                                            ->options([
                                                                'السبت' => 'السبت',
                                                                'الأحد' => 'الأحد',
                                                                'الاثنين' => 'الاثنين',
                                                                'الثلاثاء' => 'الثلاثاء',
                                                                'الأربعاء' => 'الأربعاء',
                                                                'الخميس' => 'الخميس',
                                                                'الجمعة' => 'الجمعة',
                                                            ])
                                                            ->required(),
                                                        Forms\Components\TimePicker::make('start_time')
                                                            ->label('بداية العمل')
                                                            ->default('07:00')
                                                            ->required(),
                                                        Forms\Components\TimePicker::make('end_time')
                                                            ->label('نهاية العمل')
                                                            ->default('15:00')
                                                            ->required(),
                                                        Forms\Components\Toggle::make('is_active')
                                                            ->label('يوم عمل')
                                                            ->default(true),
                                                    ]),
                                            ])
                                            ->defaultItems(7)
                                            ->addActionLabel('إضافة يوم')
                                            ->collapsible()
                                            ->cloneable(),
                                    ]),
                            ]),
                            
                        Forms\Components\Tabs\Tab::make('إعدادات عامة')
                            ->schema([
                                Forms\Components\Section::make('معلومات العميل')
                                    ->schema([
                                        Forms\Components\TextInput::make('sys_cust_code')
                                            ->label('كود العميل')
                                            ->placeholder('كود المدرسة الفريد')
                                            ->maxLength(10)
                                            ->required(),
                                    ]),
                                    
                                Forms\Components\Section::make('إعدادات التنبيهات')
                                    ->schema([
                                        Forms\Components\Toggle::make('enable_sms_notifications')
                                            ->label('تفعيل إشعارات الرسائل النصية')
                                            ->default(true),
                                        Forms\Components\Toggle::make('enable_email_notifications')
                                            ->label('تفعيل إشعارات البريد الإلكتروني')
                                            ->default(true),
                                        Forms\Components\Toggle::make('enable_push_notifications')
                                            ->label('تفعيل الإشعارات الفورية')
                                            ->default(true),
                                    ]),
                            ]),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    protected function getFormData(): array
    {
        // الحصول على المدرسة الحالية
        $schoolId = auth()->user()->school_id ?? 1;
        
        // الحصول على السجل الوحيد للمدرسة أو إنشاؤه إذا لم يكن موجود
        $systemSetting = SystemSetting::getSingleInstanceForSchool($schoolId);
        
        // جلب أيام الأسبوع من جدول week_days
        $weekDays = WeekDay::all();

        // إذا لم توجد أيام، إنشاء أيام افتراضية
        if ($weekDays->isEmpty()) {
            $defaultDays = [
                ['day' => 'السبت', 'time_from' => '07:00:00', 'time_to' => '15:00:00', 'day_inactive' => false],
                ['day' => 'الأحد', 'time_from' => '07:00:00', 'time_to' => '15:00:00', 'day_inactive' => false],
                ['day' => 'الاثنين', 'time_from' => '07:00:00', 'time_to' => '15:00:00', 'day_inactive' => false],
                ['day' => 'الثلاثاء', 'time_from' => '07:00:00', 'time_to' => '15:00:00', 'day_inactive' => false],
                ['day' => 'الأربعاء', 'time_from' => '07:00:00', 'time_to' => '15:00:00', 'day_inactive' => false],
                ['day' => 'الخميس', 'time_from' => '07:00:00', 'time_to' => '15:00:00', 'day_inactive' => false],
                ['day' => 'الجمعة', 'time_from' => '07:00:00', 'time_to' => '15:00:00', 'day_inactive' => true],
            ];
            
            foreach ($defaultDays as $day) {
                WeekDay::create([
                    'day' => $day['day'],
                    'time_from' => $day['time_from'],
                    'time_to' => $day['time_to'],
                    'day_inactive' => $day['day_inactive'],
                    'customer_code' => $systemSetting->sys_cust_code ?? 'DEFAULT',
                ]);
            }
            
            // إعادة جلب البيانات بعد الإنشاء
            $weekDays = WeekDay::where('customer_code', $systemSetting->sys_cust_code ?? 'DEFAULT')->get();
        }
        
        $activeCallTypes = CallType::where('school_id', $schoolId)->where('ctype_isactive', true)->pluck('ctype_id')->toArray();

        return [
            'sys_earlycall' => $systemSetting?->sys_earlycall ?? '07:00',
            'sys_return_call' => $systemSetting?->sys_return_call ?? '15:00',
            'sys_earlyexit' => $systemSetting?->sys_earlyexit ?? '14:00',
            'sys_exit_togat' => $systemSetting?->sys_exit_togat ?? '12:00',
            'sys_cust_code' => $systemSetting?->sys_cust_code ?? '',
            'active_call_types' => $activeCallTypes,
            'week_days' => $weekDays->map(function ($day) {
                return [
                    'day_id' => $day->day_id,
                    'day_name' => $day->day,
                    'start_time' => substr($day->time_from, 0, 5), // تحويل من HH:MM:SS إلى HH:MM
                    'end_time' => substr($day->time_to, 0, 5),     // تحويل من HH:MM:SS إلى HH:MM
                    'is_active' => !$day->day_inactive,
                ];
            })->toArray(),
            'enable_sms_notifications' => true,
            'enable_email_notifications' => true,
            'enable_push_notifications' => true,
        ];
    }

    public function save(): void
    {
        $this->saving = true;
        $data = $this->form->getState();

        try {
            // الحصول على المدرسة الحالية
            $schoolId = auth()->user()->school_id ?? 1;
            
            // الحصول على السجل الخاص بالمدرسة وتحديثه
            $systemSetting = SystemSetting::getSingleInstanceForSchool($schoolId);
            $systemSetting->update([
                'sys_earlycall' => $data['sys_earlycall'],
                'sys_return_call' => $data['sys_return_call'],
                'sys_earlyexit' => $data['sys_earlyexit'],
                'sys_exit_togat' => $data['sys_exit_togat'],
                'sys_cust_code' => $data['sys_cust_code'],
                'sys_udate' => now(),
            ]);

            // حفظ أنواع الندائات النشطة للمدرسة
            if (isset($data['active_call_types'])) {
                CallType::where('school_id', $schoolId)->update(['ctype_isactive' => false]);
                CallType::where('school_id', $schoolId)
                        ->whereIn('ctype_id', $data['active_call_types'])
                        ->update(['ctype_isactive' => true]);
            }

            // حفظ أيام الأسبوع للمدرسة
            if (isset($data['week_days'])) {
                WeekDay::where('customer_code', $data['sys_cust_code'] ?? 'DEFAULT')->delete();
                foreach ($data['week_days'] as $weekDay) {
                    WeekDay::create([
                        'day' => $weekDay['day_name'],
                        'time_from' => $weekDay['start_time'],
                        'time_to' => $weekDay['end_time'],
                        'day_inactive' => !($weekDay['is_active'] ?? true),
                        'customer_code' => $data['sys_cust_code'] ?? 'DEFAULT',
                    ]);
                }
            }

            Notification::make()
                ->title('تم حفظ الإعدادات بنجاح')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('خطأ في حفظ الإعدادات')
                ->body('حدث خطأ أثناء حفظ الإعدادات: ' . $e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->saving = false;
        }
    }

    
}