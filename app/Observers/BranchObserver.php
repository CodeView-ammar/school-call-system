<?php

namespace App\Observers;

use App\Models\Branch;

class BranchObserver
{
    /**
     * Handle the Branch "created" event.
     */
    public function created(Branch $branch): void
    {
        $this->updateSchoolBranchCount($branch);
    }

    /**
     * Handle the Branch "deleted" event.
     */
    public function deleted(Branch $branch): void
    {
        $this->updateSchoolBranchCount($branch);
    }

    /**
     * تحديث عدد الفروع في المدرسة
     */
    private function updateSchoolBranchCount(Branch $branch): void
    {
        if ($branch->school) {
            $branch->school->updateBranchCount();
        }
    }
}