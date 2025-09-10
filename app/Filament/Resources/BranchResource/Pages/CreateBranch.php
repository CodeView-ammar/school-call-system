<?php

namespace App\Filament\Resources\BranchResource\Pages;

use App\Filament\Resources\BranchResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateBranch extends CreateRecord
{
    protected static string $resource = BranchResource::class;

    protected function beforeCreate(): void
    {
        $schoolId = $this->data['school_id'] ?? auth()->user()?->school_id;

        $school = \App\Models\School::find($schoolId);

        if (!$school) {
            Notification::make()
                ->title('خطأ')
                ->body('المدرسة غير موجودة.')
                ->danger()
                ->send();

            $this->halt(); // يوقف الحفظ تمامًا
        }

        if (!$school->canAddMoreBranches()) {
            Notification::make()
                ->title('خطأ')
                ->body("وصلت المدرسة للحد الأقصى من الفروع ({$school->max_branches} فروع).")
                ->danger()
                ->send();

            $this->halt(); // يوقف الحفظ
        }
    }

    // public $latitude = 24.7136;
    // public $longitude = 46.6753;
}
