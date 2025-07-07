<?php

namespace App\Filament\Widgets;

use App\Models\School;
use App\Models\Student;
use App\Models\Bus;
use App\Models\Attendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        
        // إحصائيات للمدير العام فقط
        if ($user->hasRole('super-admin')) {
            return [
                Stat::make('إجمالي المدارس', School::count())
                    ->description('المدارس المسجلة في النظام')
                    ->descriptionIcon('heroicon-m-academic-cap')
                    ->color('primary'),
                    
                Stat::make('إجمالي الطلاب', Student::count())
                    ->description('جميع الطلاب في النظام')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('success'),
                    
                Stat::make('إجمالي الحافلات', Bus::count())
                    ->description('جميع الحافلات المسجلة')
                    ->descriptionIcon('heroicon-m-truck')
                    ->color('warning'),
                    
                Stat::make('الحضور اليوم', Attendance::whereDate('date', today())->where('status', 'present')->count())
                    ->description('حضور الطلاب اليوم')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('info'),
            ];
        }
        
        // إحصائيات مبسطة للأدوار الأخرى
        $schoolIds = $user->schools->pluck('id');
        
        return [
            Stat::make('الطلاب', Student::whereIn('branch_id', function($query) use ($schoolIds) {
                $query->select('id')->from('branches')->whereIn('school_id', $schoolIds);
            })->count())
                ->description('طلاب مدرستك')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
                
            Stat::make('الحافلات', Bus::whereIn('branch_id', function($query) use ($schoolIds) {
                $query->select('id')->from('branches')->whereIn('school_id', $schoolIds);
            })->count())
                ->description('حافلات مدرستك')
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning'),
                
            Stat::make('الحضور اليوم', Attendance::whereDate('date', today())
                ->where('status', 'present')
                ->whereIn('student_id', function($query) use ($schoolIds) {
                    $query->select('id')->from('students')
                          ->whereIn('branch_id', function($subQuery) use ($schoolIds) {
                              $subQuery->select('id')->from('branches')->whereIn('school_id', $schoolIds);
                          });
                })->count())
                ->description('حضور اليوم')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
        ];
    }
}