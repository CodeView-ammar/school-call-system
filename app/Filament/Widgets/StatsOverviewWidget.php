<?php

namespace App\Filament\Widgets;

use App\Models\School;
use App\Models\Student;
use App\Models\Bus;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\AcademicBand;
use App\Models\EducationLevel;
use App\Models\Guardian;
use App\Models\Supervisor;
use App\Models\GradeClass;
use App\Models\EarlyArrival;
use App\Models\StudentCall;
use App\Models\Gate;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;
use App\Filament\Resources\StudentResource;
class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        
        // إحصائيات للمدير العام فقط
        if ($user->hasRole('super_admin')) {
            return [
                Stat::make('إجمالي المدارس', School::count())
                    ->description('المدارس المسجلة في النظام')
                    ->descriptionIcon('heroicon-m-academic-cap')
                    ->color('primary'),

                Stat::make('إجمالي الفروع', Branch::count())
                    ->description('جميع الفروع في النظام')
                    ->descriptionIcon('heroicon-m-building-office')
                    ->color('info'),
                    
                Stat::make('إجمالي الطلاب', Student::count())
                    ->description('جميع الطلاب في النظام')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('success'),

                Stat::make('إجمالي الفرق', AcademicBand::count())
                    ->description('جميع الفرق الأكاديمية')
                    ->descriptionIcon('heroicon-m-squares-plus')
                    ->color('warning'),

                Stat::make('إجمالي المراحل الدراسية', EducationLevel::count())
                    ->description('جميع المراحل الدراسية')
                    ->descriptionIcon('heroicon-m-book-open')
                    ->color('purple'),

                Stat::make('إجمالي أولياء الأمور', Guardian::count())
                    ->description('جميع أولياء الأمور')
                    ->descriptionIcon('heroicon-m-users')
                    ->color('emerald'),

                Stat::make('إجمالي المساعدين', Supervisor::count())
                    ->description('جميع المساعدين')
                    ->descriptionIcon('heroicon-m-user-circle')
                    ->color('blue'),

                Stat::make('إجمالي الفصول الدراسية', GradeClass::count())
                    ->description('جميع الفصول الدراسية')
                    ->descriptionIcon('heroicon-m-rectangle-group')
                    ->color('indigo'),

                Stat::make('طلبات الخروج المبكر', EarlyArrival::count())
                    ->description('جميع طلبات الخروج المبكر')
                    ->descriptionIcon('heroicon-m-clock')
                    ->color('orange'),

                Stat::make('طلبات الاستعداد', StudentCall::where('status', 'ready')->count())
                    ->description('طلبات الاستعداد')
                    ->descriptionIcon('heroicon-m-check-badge')
                    ->color('green'),

                Stat::make('طلبات الخروج على البوابة', StudentCall::where('status', 'at_gate')->count())
                    ->description('طلبات الخروج على البوابة')
                    ->descriptionIcon('heroicon-m-home-modern')
                    ->color('yellow'),

                Stat::make('طلبات تأكيد الاستلام', StudentCall::where('status', 'picked_up')->count())
                    ->description('طلبات تأكيد الاستلام')
                    ->descriptionIcon('heroicon-m-hand-thumb-up')
                    ->color('teal'),

                Stat::make('إجمالي الشاشات', Gate::count())
                    ->description('جميع الشاشات (البوابات)')
                    ->descriptionIcon('heroicon-m-tv')
                    ->color('slate'),
                    
                Stat::make('إجمالي الحافلات', Bus::count())
                    ->description('جميع الحافلات المسجلة')
                    ->descriptionIcon('heroicon-m-truck')
                    ->color('amber'),

                Stat::make('الحضور اليوم', Attendance::whereDate('attendance_date', today())->where('status', 'present')->count())
                    ->description('حضور الطلاب اليوم')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('lime'),
            ];
        }
        
        // إحصائيات للأدوار الأخرى حسب المدرسة
        $schoolIds = [$user->school_id];
        
        return [

                Stat::make('الفروع', Branch::whereIn('school_id', $schoolIds)->count())
                            ->description('فروع مدرستك')
                            ->descriptionIcon('heroicon-m-building-office')
                            ->color('info')
                            ->url(\App\Filament\Resources\BranchResource::getUrl()),

            Stat::make('الطلاب', function () use ($schoolIds) {
                return Student::whereIn('branch_id', function ($query) use ($schoolIds) {
                    $query->select('id')->from('branches')->whereIn('school_id', $schoolIds);
                })->count();
            })
            ->description('طلاب مدرستك')
            ->descriptionIcon('heroicon-m-user-group')
            ->color('success')
            ->url(StudentResource::getUrl()), // استخدام method getUrl() من Filament Resource
            
            Stat::make('الفرق الأكاديمية', AcademicBand::whereIn('school_id', $schoolIds)->count())
            ->description('فرق مدرستك')
            ->descriptionIcon('heroicon-m-squares-plus')
            ->color('warning')
            ->url(\App\Filament\Resources\AcademicBandResource::getUrl()), // استخدام method getUrl() من Filament Resource
                


            
            Stat::make('المراحل الدراسية', EducationLevel::whereIn('school_id', $schoolIds)->count())
                ->description('مراحل مدرستك')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('purple')
                ->url(\App\Filament\Resources\EducationLevelResource::getUrl()),

            Stat::make('أولياء الأمور', Guardian::whereIn('school_id', $schoolIds)->count())
                ->description('أولياء أمور مدرستك')
                ->descriptionIcon('heroicon-m-users')
                ->color('emerald')
                ->url(\App\Filament\Resources\GuardianResource::getUrl()),

            Stat::make('المساعدين', Supervisor::whereIn('school_id', $schoolIds)->count())
                ->description('مساعدين مدرستك')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('blue')
                ->url(\App\Filament\Resources\SupervisorResource::getUrl()),

            Stat::make('الفصول الدراسية', GradeClass::whereIn('branch_id', function($query) use ($schoolIds) {
                $query->select('id')->from('branches')->whereIn('school_id', $schoolIds);
            })->count())
                ->description('فصول مدرستك')
                ->descriptionIcon('heroicon-m-rectangle-group')
                ->color('indigo')
                ->url(\App\Filament\Resources\GradeClassResource::getUrl()),

            Stat::make('طلبات الخروج المبكر', EarlyArrival::whereIn('student_id', function($query) use ($schoolIds) {
                $query->select('id')->from('students')
                      ->whereIn('branch_id', function($subQuery) use ($schoolIds) {
                          $subQuery->select('id')->from('branches')->whereIn('school_id', $schoolIds);
                      });
            })->count())
                ->description('طلبات الخروج المبكر')
                ->descriptionIcon('heroicon-m-clock')
                ->color('orange')
                ->url(\App\Filament\Resources\EarlyArrivalResource::getUrl()),

            Stat::make('طلبات الاستعداد', StudentCall::where('status', 'ready')
                ->whereIn('student_id', function($query) use ($schoolIds) {
                    $query->select('id')->from('students')
                          ->whereIn('branch_id', function($subQuery) use ($schoolIds) {
                              $subQuery->select('id')->from('branches')->whereIn('school_id', $schoolIds);
                          });
                })->count())
                ->description('طلبات الاستعداد')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('green')
                ->url(\App\Filament\Resources\StudentCallResource::getUrl()),

            Stat::make('طلبات على البوابة', StudentCall::where('status', 'at_gate')
                ->whereIn('student_id', function($query) use ($schoolIds) {
                    $query->select('id')->from('students')
                          ->whereIn('branch_id', function($subQuery) use ($schoolIds) {
                              $subQuery->select('id')->from('branches')->whereIn('school_id', $schoolIds);
                          });
                })->count())
                ->description('طلبات على البوابة')
                ->descriptionIcon('heroicon-m-home-modern')
                ->color('yellow')
                ->url(\App\Filament\Resources\StudentCallResource::getUrl()),

            Stat::make('تأكيد الاستلام', StudentCall::where('status', 'picked_up')
                ->whereIn('student_id', function($query) use ($schoolIds) {
                    $query->select('id')->from('students')
                          ->whereIn('branch_id', function($subQuery) use ($schoolIds) {
                              $subQuery->select('id')->from('branches')->whereIn('school_id', $schoolIds);
                          });
                })->count())
                ->description('تأكيد الاستلام')
                ->descriptionIcon('heroicon-m-hand-thumb-up')
                ->color('teal')
                ->url(\App\Filament\Resources\StudentCallResource::getUrl()),

            Stat::make('الشاشات', Gate::whereIn('school_id', $schoolIds)->count())
                ->description('شاشات مدرستك')
                ->descriptionIcon('heroicon-m-tv')
                ->color('slate')
                ->url(\App\Filament\Resources\GateResource::getUrl()),
                
            Stat::make('الحافلات', Bus::whereIn('branch_id', function($query) use ($schoolIds) {
                $query->select('id')->from('branches')->whereIn('school_id', $schoolIds);
            })->count())
                ->description('حافلات مدرستك')
                ->descriptionIcon('heroicon-m-truck')
                ->color('amber')
                ->url(\App\Filament\Resources\BusResource::getUrl()),

            Stat::make('الحضور اليوم', Attendance::whereDate('attendance_date', today())
                ->where('status', 'present')
                ->whereIn('student_id', function($query) use ($schoolIds) {
                    $query->select('id')->from('students')
                          ->whereIn('branch_id', function($subQuery) use ($schoolIds) {
                              $subQuery->select('id')->from('branches')->whereIn('school_id', $schoolIds);
                          });
                })->count())
                ->description('حضور اليوم')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('lime')
                ->url(\App\Filament\Resources\AttendanceResource::getUrl()),
        ];
    }
}
