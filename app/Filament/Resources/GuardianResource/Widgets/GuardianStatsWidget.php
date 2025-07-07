<?php

namespace App\Filament\Resources\GuardianResource\Widgets;

use App\Models\Guardian;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GuardianStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalGuardians = Guardian::count();
        $activeGuardians = Guardian::where('is_active', true)->count();
        $totalStudents = Student::count();
        $studentsWithGuardians = Student::whereHas('guardians')->count();
        $studentsWithoutGuardians = $totalStudents - $studentsWithGuardians;

        return [
            Stat::make('إجمالي أولياء الأمور', $totalGuardians)
                ->description('العدد الكلي لأولياء الأمور في النظام')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('أولياء الأمور النشطين', $activeGuardians)
                ->description("من أصل {$totalGuardians} ولي أمر")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('الطلاب المرتبطين', $studentsWithGuardians)
                ->description("من أصل {$totalStudents} طالب")
                ->descriptionIcon('heroicon-m-link')
                ->color('info'),

            Stat::make('طلاب بدون أولياء أمور', $studentsWithoutGuardians)
                ->description('يحتاجون إلى ربط مع أولياء أمور')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($studentsWithoutGuardians > 0 ? 'warning' : 'success'),
        ];
    }
}