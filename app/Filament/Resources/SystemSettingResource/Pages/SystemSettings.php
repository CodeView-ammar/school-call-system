<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
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

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'filament.pages.system-settings';
    protected static ?string $title = 'إعدادات النظام';
    protected static ?string $navigationLabel = 'إعدادات النظام';
    protected static ?string $navigationGroup = 'إدارة النظام';
    protected static ?int $navigationSort = 1;

    public ?array $data = [];

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
                        Forms\Components\Tabs\Tab::make('إعدادات المكالمات')
                            ->schema([
                                Forms\Components\Section::make('أوقات المكالمات')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TimePicker::make('sys_earlycall')
                                                    ->label('وقت المكالمة المبكرة')
                                                    ->default('07:00')
                                                    ->required(),
                                                Forms\Components\TimePicker::make('sys_return_call')
                                                    ->label('وقت مكالمة العودة')
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
                                    
                                Forms\Components\Section::make('أنواع المكالمات النشطة')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('active_call_types')
                                            ->label('أنواع المكالمات المفعلة')
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
        $systemSetting = SystemSetting::first();
        $weekDays = WeekDay::all();
        $activeCallTypes = CallType::where('ctype_isactive', true)->pluck('ctype_id')->toArray();

        return [
            'sys_earlycall' => $systemSetting?->sys_earlycall ?? '07:00',
            'sys_return_call' => $systemSetting?->sys_return_call ?? '15:00',
            'sys_earlyexit' => $systemSetting?->sys_earlyexit ?? '14:00',
            'sys_exit_togat' => $systemSetting?->sys_exit_togat ?? '12:00',
            'sys_cust_code' => $systemSetting?->sys_cust_code ?? '',
            'active_call_types' => $activeCallTypes,
            'week_days' => $weekDays->map(function ($day) {
                return [
                    'day_name' => $day->day,
                    'start_time' => $day->time_from,
                    'end_time' => $day->time_to,
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
        $data = $this->form->getState();

        try {
            // حفظ إعدادات النظام الرئيسية
            SystemSetting::updateOrCreate(
                ['sys_id' => 1],
                [
                    'sys_earlycall' => $data['sys_earlycall'],
                    'sys_return_call' => $data['sys_return_call'],
                    'sys_earlyexit' => $data['sys_earlyexit'],
                    'sys_exit_togat' => $data['sys_exit_togat'],
                    'sys_cust_code' => $data['sys_cust_code'],
                    'sys_udate' => now(),
                ]
            );

            // حفظ أنواع المكالمات النشطة
            if (isset($data['active_call_types'])) {
                CallType::query()->update(['ctype_isactive' => false]);
                CallType::whereIn('ctype_id', $data['active_call_types'])
                        ->update(['ctype_isactive' => true]);
            }

            // حفظ أيام الأسبوع
            if (isset($data['week_days'])) {
                WeekDay::truncate();
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
        }
    }

    public function getActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('save')
                ->label('حفظ جميع الإعدادات')
                ->color('primary')
                ->action('save')
                ->keyBindings(['mod+s']),
        ];
    }
}