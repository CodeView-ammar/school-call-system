<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SupervisorController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\GuardianController;
use App\Http\Controllers\Api\SchoolController;
use App\Http\Controllers\Api\AuthController;

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
    
    // Demo route for testing without authentication
    Route::get('demo/login', function () {
        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'data' => [
                'token' => 'demo-token-' . time(),
                'token_type' => 'Bearer',
                'user' => [
                    'id' => 1,
                    'name' => 'مدير النظام',
                    'email' => 'admin@school.sa',
                    'role' => 'admin',
                    'created_at' => now()->toISOString(),
                    'updated_at' => now()->toISOString(),
                ],
            ],
        ]);
    });
});
    
    // Schools routes
    Route::apiResource('schools', SchoolController::class);
    Route::get('schools/{school}/statistics', [SchoolController::class, 'statistics']);

    // Supervisors routes
    Route::apiResource('supervisors', SupervisorController::class);
    Route::put('supervisors/{supervisor}/toggle-status', [SupervisorController::class, 'toggleStatus']);
    Route::get('supervisors/{supervisor}/statistics', [SupervisorController::class, 'statistics']);
    Route::get('supervisors/search', [SupervisorController::class, 'search']);
    
    // Supervisor-Student relationship routes
    Route::post('supervisors/{supervisor}/students', [SupervisorController::class, 'attachStudent']);
    Route::delete('supervisors/{supervisor}/students/{student}', [SupervisorController::class, 'detachStudent']);
    
    // Supervisor-Guardian relationship routes
    Route::post('supervisors/{supervisor}/guardians', [SupervisorController::class, 'attachGuardian']);
    Route::delete('supervisors/{supervisor}/guardians/{guardian}', [SupervisorController::class, 'detachGuardian']);

    // Students routes
    Route::apiResource('students', StudentController::class);
    Route::post('students/{student}/upload-photo', [StudentController::class, 'uploadPhoto']);
    Route::get('students/search', [StudentController::class, 'search']);
    
    // Student Export routes
    Route::get('students/export', [\App\Http\Controllers\Api\StudentExportController::class, 'exportToExcel']);
    Route::get('students/export/statistics', [\App\Http\Controllers\Api\StudentExportController::class, 'getExportStatistics']);
    
    // Student Restore routes
    Route::prefix('students/restore')->group(function () {
        Route::get('options', [\App\Http\Controllers\Api\StudentRestoreController::class, 'getRestoreOptions']);
        Route::post('backup', [\App\Http\Controllers\Api\StudentRestoreController::class, 'createBackup']);
        Route::get('backups', [\App\Http\Controllers\Api\StudentRestoreController::class, 'getBackups']);
        Route::post('from-backup', [\App\Http\Controllers\Api\StudentRestoreController::class, 'restoreFromBackup']);
        Route::post('reset-attendance', [\App\Http\Controllers\Api\StudentRestoreController::class, 'resetAttendance']);
        Route::post('refresh-from-server', [\App\Http\Controllers\Api\StudentRestoreController::class, 'refreshFromServer']);
        Route::delete('backup/{backup_id}', [\App\Http\Controllers\Api\StudentRestoreController::class, 'deleteBackup']);
    });
    
    // Student-Guardian relationship routes
    Route::post('students/{student}/guardians', [StudentController::class, 'attachGuardian']);
    Route::delete('students/{student}/guardians/{guardian}', [StudentController::class, 'detachGuardian']);

    // Guardians routes
    Route::apiResource('guardians', GuardianController::class);
    Route::get('guardians/search', [GuardianController::class, 'search']);
    
    // Guardian-Supervisor relationship routes
    Route::post('guardians/{guardian}/supervisors', [GuardianController::class, 'attachSupervisor']);
    Route::delete('guardians/{guardian}/supervisors/{supervisor}', [GuardianController::class, 'detachSupervisor']);

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
    
