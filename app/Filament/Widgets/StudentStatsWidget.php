<?php

namespace App\Filament\Widgets;

use App\Models\Student;
// use App\Models\AttendanceBackup;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudentStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $query = Student::query();
        
        // تطبيق فلتر المدرسة إذا كان المستخدم مرتبط بمدرسة معينة
        if (auth()->user()?->school_id) {
            $query->where('school_id', auth()->user()->school_id);
        }

        $totalStudents = $query->count();
        $activeStudents = $query->where('is_active', true)->count();
        $presentStudents = $query->where('is_present', true)->count();
        $maleStudents = $query->where('gender', 'male')->count();
        $femaleStudents = $query->where('gender', 'female')->count();
        // $totalBackups = AttendanceBackup::count();

        return [
            Stat::make('إجمالي الطلاب', $totalStudents)
                ->description('العدد الكلي للطلاب المسجلين')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([7, 12, 8, 15, 18, 22, $totalStudents]),

            Stat::make('الطلاب النشطين', $activeStudents)
                ->description('الطلاب النشطين حالياً')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([5, 8, 12, 15, 18, 20, $activeStudents]),

            Stat::make('الحضور اليوم', $presentStudents)
                ->description(sprintf('%.1f%% من إجمالي الطلاب', $totalStudents > 0 ? ($presentStudents / $totalStudents) * 100 : 0))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($presentStudents / max($totalStudents, 1) > 0.8 ? 'success' : 'warning'),

            Stat::make('الطلاب الذكور', $maleStudents)
                ->description(sprintf('%.1f%% من الإجمالي', $totalStudents > 0 ? ($maleStudents / $totalStudents) * 100 : 0))
                ->descriptionIcon('heroicon-m-user')
                ->color('info'),

            Stat::make('الطالبات', $femaleStudents)
                ->description(sprintf('%.1f%% من الإجمالي', $totalStudents > 0 ? ($femaleStudents / $totalStudents) * 100 : 0))
                ->descriptionIcon('heroicon-m-user')
                ->color('pink'),

            // Stat::make('النسخ الاحتياطية', $totalBackups)
            //     ->description('عدد النسخ الاحتياطية المحفوظة')
            //     ->descriptionIcon('heroicon-m-shield-check')
            //     ->color('gray'),
        ];
    }
}