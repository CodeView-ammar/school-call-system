<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SupervisorController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\GuardianController;
use App\Http\Controllers\Api\SchoolController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BusController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\StudentCallLogController;
use App\Http\Controllers\Api\WeekDayController;
use App\Http\Controllers\Api\UserController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Authentication routes (public)
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth user routes
    Route::get('auth/user', [AuthController::class, 'user']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('users/{id}/change-password', [UserController::class, 'changePassword']);

});

    // Schools routes
    Route::apiResource('schools', SchoolController::class);
    Route::get('schools/{school}/statistics', [SchoolController::class, 'statistics']);
    Route::get('schools/{school}/classes', [SchoolController::class, 'classes']);
    Route::get('branches/{branch}/students', [SchoolController::class, 'studentsByBranch']);
    Route::get('students/filter', [StudentController::class, 'studentsByBranchClassSchool']);

    // Branches routes
    Route::apiResource('branches', BranchController::class);
    Route::get('branches/{branch}/statistics', [BranchController::class, 'statistics']);
    Route::get('schools/{school_id}/branches', [BranchController::class, 'getBySchool']);
    Route::get('schools/{school_id}/branches/names', [BranchController::class, 'getBranchNames']);

    // Supervisors routes
    Route::apiResource('supervisors', SupervisorController::class);
    Route::get('supervisors/{id}/toggle-status', [SupervisorController::class, 'toggleStatus']);
    Route::get('supervisors/{supervisor}/statistics', [SupervisorController::class, 'statistics']);
    Route::get('supervisors/search', [SupervisorController::class, 'search']);
    Route::get('supervisors-with-students', [SupervisorController::class, 'getSupervisorsWithGuardiansAndStudents']);

    //bus
    Route::get('/guardian/buses', [BusController::class, 'getBusesByGuardianAndSchool']);
    Route::get('/guardian/students-with-buses', [BusController::class, 'getStudentsWithBusesByGuardianAndSchool']);
    Route::get('/buses/driver-branches', [BusController::class, 'getDriverBranches']);
    Route::get('/students/by-driver-branch', [BusController::class, 'getStudentsByDriverAndBranch']);

    Route::post('/attendance', [AttendanceController::class, 'store']);
    Route::get('/attendance/{studentId}', [AttendanceController::class, 'show']);
    Route::put('/attendance/{id}', [AttendanceController::class, 'update']);
    
    
    // Supervisor-Student relationship routes
    Route::post('supervisors/{supervisor}/students', [SupervisorController::class, 'attachStudent']);
    Route::delete('supervisors/{supervisor}/students/{student}', [SupervisorController::class, 'detachStudent']);

    // Supervisor-Guardian relationship routes
    Route::post('supervisors/{supervisor}/guardians', [SupervisorController::class, 'attachGuardian']);
    Route::delete('supervisors/{supervisor}/guardians/{guardian}', [SupervisorController::class, 'detachGuardian']);
    // Students routes
    Route::get('students/by-school', [StudentController::class, 'getStudentsByUserAndSchool']);

    Route::apiResource('students', StudentController::class);
    Route::get('/students/by-user-and-school', [StudentController::class, 'getStudentsByUserAndSchoolnotbranch']);
    Route::post('students/{student}/upload-photo', [StudentController::class, 'uploadPhoto']);
    Route::get('students/search', [StudentController::class, 'search']);

    // Student Export routes
    Route::get('students/export', [\App\Http\Controllers\Api\StudentExportController::class, 'exportToExcel']);
    Route::get('students/export/statistics', [\App\Http\Controllers\Api\StudentExportController::class, 'getExportStatistics']);

   
        
    // Student calls routes

    Route::put('student-calls/{id}/status', [App\Http\Controllers\Api\StudentCallController::class, 'updateStatus']);
    Route::get('student-calls/{student_id}/today-latest',[App\Http\Controllers\Api\StudentCallController::class, 'todayLatestByStudent']);
    

    // Student calls log routes
    Route::get('student-calls-logs/', [StudentCallLogController::class, 'getTodayCallLog']);

    // جلب جميع الندائات مع إمكانية التصفية
    Route::get('/student-calls', [App\Http\Controllers\Api\StudentCallController::class, 'index']);
    
    // إنشاء نداء جديد
    Route::post('/student-calls', [App\Http\Controllers\Api\StudentCallController::class, 'store']);
    
    // جلب نداء محدد
    Route::get('/student-calls/{studentCall}', [App\Http\Controllers\Api\StudentCallController::class, 'show']);
    
    // تحديث نداء محدد
    Route::put('/student-calls/{studentCall}', [App\Http\Controllers\Api\StudentCallController::class, 'update']);
    
    // حذف نداء محدد
    Route::delete('/student-calls/{studentCall}', [App\Http\Controllers\Api\StudentCallController::class, 'destroy']);
    
    // تحديث حالة النداء فقط
    Route::put('/student-calls/{id}/status', [App\Http\Controllers\Api\StudentCallController::class, 'updateStatus']);
    
    // جلب آخر نداء لطالب معين في اليوم الحالي
    Route::get('/student-calls/{studentId}/today-latest', [App\Http\Controllers\Api\StudentCallController::class, 'todayLatestByStudent']);
    
    // فحص وجود نداء لطالب في تاريخ معين
    Route::post('/student-calls/check', [App\Http\Controllers\Api\StudentCallController::class, 'checkStudentCall']);
    
    // إحصائيات الندائات
    Route::get('/student-calls/statistics', [App\Http\Controllers\Api\StudentCallController::class, 'statistics']);

    // Student-Guardian relationship routes
    Route::post('students/{student}/guardians', [StudentController::class, 'attachGuardian']);
    Route::delete('students/{student}/guardians/{guardian}', [StudentController::class, 'detachGuardian']);

    // Guardians routes
    Route::apiResource('guardians', GuardianController::class);
    Route::get('guardians/search', [GuardianController::class, 'search']);

    // Guardian-Supervisor relationship routes
    Route::post('guardians/{guardian}/supervisors', [GuardianController::class, 'attachSupervisor']);
    Route::delete('guardians/{guardian}/supervisors/{supervisor}', [GuardianController::class, 'detachSupervisor']);


    Route::prefix('holidays')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\HolidayController::class, 'index']);
    });

    Route::prefix('week-days')->group(function () {
        Route::get('/', [WeekDayController::class, 'index']);
        Route::get('/{id}', [WeekDayController::class, 'show']);
        Route::post('/', [WeekDayController::class, 'store']);
        Route::put('/{id}', [WeekDayController::class, 'update']);
        Route::delete('/{id}', [WeekDayController::class, 'destroy']);
    });


    Route::get('/check-version', [App\Http\Controllers\Api\VersionController::class, 'check']);

    // Statistics route
    Route::get('statistics', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'total_schools' => \App\Models\School::count(),
                'total_students' => \App\Models\Student::count(),
                'active_students' => \App\Models\Student::where('is_active', true)->count(),
                'total_supervisors' => \App\Models\Supervisor::count(),
                'active_supervisors' => \App\Models\Supervisor::where('is_active', true)->count(),
                'total_guardians' => \App\Models\Guardian::count(),
                'active_guardians' => \App\Models\Guardian::where('is_active', true)->count(),
                'male_students' => \App\Models\Student::where('gender', 'male')->count(),
                'female_students' => \App\Models\Student::where('gender', 'female')->count(),
            ]
        ]);
    });