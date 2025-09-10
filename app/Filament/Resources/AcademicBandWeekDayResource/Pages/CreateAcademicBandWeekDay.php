<?php

namespace App\Filament\Resources\AcademicBandWeekDayResource\Pages;

use App\Filament\Resources\AcademicBandWeekDayResource;
use App\Models\AcademicBandWeekDay;
use App\Models\WeekDay;
use App\Models\AcademicBand;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateAcademicBandWeekDay extends CreateRecord
{
    protected static string $resource = AcademicBandWeekDayResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء الجدول بنجاح';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // إضافة school_id إذا لم يكن موجوداً (للمستخدمين غير الأدمن)
        if (!isset($data['school_id']) && auth()->user()?->school_id) {
            $data['school_id'] = auth()->user()->school_id;
        }

        // التحقق من التكرار قبل محاولة الحفظ
        $exists = AcademicBandWeekDay::where('school_id', $data['school_id'])
            ->where('academic_band_id', $data['academic_band_id'])
            ->where('week_day_id', $data['week_day_id'])
            ->exists();

        if ($exists) {
            // الحصول على تفاصيل للرسالة
            $weekDay = WeekDay::where('school_id', $data['school_id'])
                ->where('day_id', $data['week_day_id'])
                ->first();
            
            $academicBand = AcademicBand::find($data['academic_band_id']);
            
            $dayName = $weekDay ? $weekDay->day : 'هذا اليوم';
            $bandName = $academicBand ? $academicBand->name_ar : 'هذه الفرقة';

            // إرسال إشعار مفصل
            Notification::make()
                ->title('❌ فشل الحفظ: يوجد تكرار')
                ->body("يوم {$dayName} مسجل مسبقاً للفرقة {$bandName}. يرجى اختيار يوم آخر.")
                ->danger()
                ->persistent()
                ->send();

            // رمي استثناء validation لمنع الحفظ
            throw ValidationException::withMessages([
                'week_day_id' => "يوم {$dayName} مسجل مسبقاً لهذه الفرقة",
            ]);
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return static::getModel()::create($data);
        } catch (\Exception $e) {
            // معالجة أي أخطاء من قاعدة البيانات (مثل القيد الفريد)
            if (str_contains($e->getMessage(), 'Duplicate entry') || 
                str_contains($e->getMessage(), 'unique_school_band_day')) {
                
                Notification::make()
                    ->title('❌ خطأ في قاعدة البيانات')
                    ->body('يوجد تكرار في البيانات. هذا اليوم مسجل مسبقاً لهذه الفرقة.')
                    ->danger()
                    ->persistent()
                    ->send();

                // رمي استثناء validation
                throw ValidationException::withMessages([
                    'week_day_id' => 'يوجد تكرار في البيانات',
                ]);
            }

            // إعادة رمي الاستثناء إذا لم يكن متعلقاً بالتكرار
            throw $e;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('رجوع')
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-o-arrow-right'),
        ];
    }
    
}
