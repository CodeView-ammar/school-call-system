<?php

namespace App\Filament\Resources\WeekDayResource\Pages;

use App\Filament\Resources\WeekDayResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWeekDay extends EditRecord
{
    protected static string $resource = WeekDayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('حذف'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // إذا كان المستخدم مدير مدرسة، قم بتعيين school_id تلقائياً
        if (auth()->user()?->school_id && !isset($data['school_id'])) {
            $data['school_id'] = auth()->user()->school_id;
        }

        // تحويل day_inactive إلى قيمة صحيحة
        $data['day_inactive'] = isset($data['day_inactive']) && $data['day_inactive'] ? 1 : 0;

        return $data;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تحديث يوم الأسبوع بنجاح';
    }
}
