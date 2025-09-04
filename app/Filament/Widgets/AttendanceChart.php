<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceChart extends ChartWidget
{
    protected static ?string $heading = 'إحصائيات الحضور الأسبوعية';
    
    protected static string $color = 'info';
    
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $user = Auth::user();
        $now = Carbon::now();
        $weekAgo = $now->copy()->subDays(7);
        
        // جمع البيانات حسب الأيام السابقة
        $days = [];
        $presentData = [];
        $absentData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $days[] = $date->translatedFormat('D');

            $query = Attendance::whereDate('attendance_date', $date);

            // تصفية حسب صلاحيات المستخدم
            if (!$user->hasRole('super_admin')) {
                $schoolIds = $user->schools->pluck('id');
                $query->whereIn('student_id', function($subQuery) use ($schoolIds) {
                    $subQuery->select('id')->from('students')
                            ->whereIn('branch_id', function($branchQuery) use ($schoolIds) {
                                $branchQuery->select('id')->from('branches')->whereIn('school_id', $schoolIds);
                            });
                });
            }
            
            $presentData[] = (clone $query)->where('status', 'present')->count();
            $absentData[] = (clone $query)->where('status', 'absent')->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'حاضر',
                    'data' => $presentData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                ],
                [
                    'label' => 'غائب',
                    'data' => $absentData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                ],
            ],
            'labels' => $days,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}