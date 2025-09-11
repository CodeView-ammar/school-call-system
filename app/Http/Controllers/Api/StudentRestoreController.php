<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentBackup;
use App\Imports\StudentsImport;
use App\Exports\StudentsExport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class StudentRestoreController extends Controller
{
    /**
     * إعادة تعيين حالة الحضور للطلاب
     */
    public function resetAttendance(Request $request): JsonResponse
    {
        try {
            $resetType = $request->get('reset_type', 'all_absent');
            $schoolId = auth()->user()?->school_id;
            
            if (!$schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن تحديد المدرسة الخاصة بك'
                ], 400);
            }

            $query = Student::where('school_id', $schoolId);
            
            switch ($resetType) {
                case 'all_present':
                    $affected = $query->update([
                        'is_present' => true,
                        'attendance_time' => now(),
                        'updated_at' => now()
                    ]);
                    $message = "تم تعيين جميع الطلاب ({$affected}) كحاضرين";
                    break;
                    
                case 'all_absent':
                    $affected = $query->update([
                        'is_present' => false,
                        'attendance_time' => null,
                        'updated_at' => now()
                    ]);
                    $message = "تم تعيين جميع الطلاب ({$affected}) كغائبين";
                    break;
                    
                case 'clear_timestamps':
                    $affected = $query->update([
                        'attendance_time' => null,
                        'updated_at' => now()
                    ]);
                    $message = "تم مسح أوقات الحضور لجميع الطلاب ({$affected})";
                    break;
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'نوع إعادة التعيين غير صحيح'
                    ], 400);
            }

            // تسجيل العملية في اللوج
            Log::info('Student attendance reset', [
                'user_id' => auth()->id(),
                'school_id' => $schoolId,
                'reset_type' => $resetType,
                'affected_students' => $affected,
                'notes' => $request->get('notes')
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'affected_students' => $affected,
                    'reset_type' => $resetType,
                    'timestamp' => now()->toDateTimeString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error resetting attendance: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إعادة تعيين الحضور: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * الاسترداد من نسخة احتياطية
     */
    public function restoreFromBackup(Request $request): JsonResponse
    {
        try {
            $backupId = $request->get('backup_id');
            $schoolId = auth()->user()?->school_id;
            
            if (!$schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن تحديد المدرسة الخاصة بك'
                ], 400);
            }

            if (!$backupId) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب تحديد النسخة الاحتياطية للاسترداد منها'
                ], 400);
            }

            // البحث عن النسخة الاحتياطية
            $backup = StudentBackup::where('id', $backupId)
                ->where('school_id', $schoolId)
                ->first();

            if (!$backup) {
                return response()->json([
                    'success' => false,
                    'message' => 'النسخة الاحتياطية المحددة غير موجودة'
                ], 404);
            }

            // التحقق من وجود ملف النسخة الاحتياطية
            if (!Storage::exists($backup->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ملف النسخة الاحتياطية غير موجود'
                ], 404);
            }

            DB::beginTransaction();

            try {
                // إنشاء نسخة احتياطية من الوضع الحالي قبل الاسترداد
                $currentBackup = $this->createBackupBeforeRestore($schoolId);

                // حذف الطلاب الحاليين
                Student::where('school_id', $schoolId)->delete();

                // استيراد البيانات من النسخة الاحتياطية
                $filePath = Storage::path($backup->file_path);
                $import = new StudentsImport($schoolId);
                Excel::import($import, $filePath);

                // تحديث معلومات النسخة الاحتياطية
                $backup->update([
                    'last_restored_at' => now(),
                    'restored_by' => auth()->id()
                ]);

                DB::commit();

                // تسجيل العملية في اللوج
                Log::info('Students restored from backup', [
                    'user_id' => auth()->id(),
                    'school_id' => $schoolId,
                    'backup_id' => $backupId,
                    'backup_name' => $backup->backup_name,
                    'notes' => $request->get('notes')
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "تم استرداد بيانات الطلاب من النسخة الاحتياطية '{$backup->backup_name}' بنجاح",
                    'data' => [
                        'backup_name' => $backup->backup_name,
                        'restored_students' => Student::where('school_id', $schoolId)->count(),
                        'current_backup_created' => $currentBackup['backup_name'] ?? null,
                        'restored_at' => now()->toDateTimeString()
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error restoring from backup: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الاسترداد من النسخة الاحتياطية: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * التحديث من الخادم (إعادة تحميل البيانات)
     */
    public function refreshFromServer(Request $request): JsonResponse
    {
        try {
            $schoolId = auth()->user()?->school_id;
            
            if (!$schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن تحديد المدرسة الخاصة بك'
                ], 400);
            }

            // إنشاء نسخة احتياطية قبل التحديث
            $backup = $this->createBackupBeforeRestore($schoolId);

            // إعادة تحميل العلاقات والبيانات
            $students = Student::where('school_id', $schoolId)
                ->with(['branch', 'academicBand', 'gradeClass', 'bus', 'guardians'])
                ->get();

            foreach ($students as $student) {
                $student->touch(); // تحديث الـ timestamps
            }

            // تسجيل العملية في اللوج
            Log::info('Students data refreshed from server', [
                'user_id' => auth()->id(),
                'school_id' => $schoolId,
                'students_count' => $students->count(),
                'notes' => $request->get('notes')
            ]);

            return response()->json([
                'success' => true,
                'message' => "تم تحديث بيانات الطلاب من الخادم بنجاح",
                'data' => [
                    'students_count' => $students->count(),
                    'backup_created' => $backup['backup_name'] ?? null,
                    'refreshed_at' => now()->toDateTimeString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error refreshing from server: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التحديث من الخادم: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إنشاء نسخة احتياطية جديدة
     */
    public function createBackup(Request $request): JsonResponse
    {
        try {
            $schoolId = auth()->user()?->school_id;
            $backupName = $request->get('backup_name', 'نسخة احتياطية - ' . now()->format('Y-m-d_H-i'));
            
            if (!$schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن تحديد المدرسة الخاصة بك'
                ], 400);
            }

            $backup = $this->createStudentBackup($schoolId, $backupName, $request->get('notes'));

            return response()->json([
                'success' => true,
                'message' => "تم إنشاء النسخة الاحتياطية '{$backupName}' بنجاح",
                'data' => [
                    'backup_id' => $backup['backup_id'],
                    'backup_name' => $backup['backup_name'],
                    'students_count' => $backup['students_count'],
                    'file_size' => $backup['file_size'],
                    'created_at' => $backup['created_at']
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating backup: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء النسخة الاحتياطية: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على قائمة النسخ الاحتياطية
     */
    public function getBackups(Request $request): JsonResponse
    {
        try {
            $schoolId = auth()->user()?->school_id;
            
            if (!$schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن تحديد المدرسة الخاصة بك'
                ], 400);
            }

            $backups = StudentBackup::where('school_id', $schoolId)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'message' => 'تم جلب قائمة النسخ الاحتياطية بنجاح',
                'data' => $backups
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting backups: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب النسخ الاحتياطية: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إنشاء نسخة احتياطية قبل عملية الاسترداد
     */
    private function createBackupBeforeRestore($schoolId): array
    {
        $backupName = 'نسخة احتياطية تلقائية قبل الاسترداد - ' . now()->format('Y-m-d_H-i');
        return $this->createStudentBackup($schoolId, $backupName, 'تم إنشاؤها تلقائياً قبل عملية الاسترداد');
    }

    /**
     * إنشاء نسخة احتياطية للطلاب
     */
private function createStudentBackup($schoolId, $backupName, $notes = null): array
{
    // تصفية الطلاب حسب المدرسة
    $filters = ['school_id' => $schoolId];
    $export = new StudentsExport($filters);

    // اسم الملف
    $filename = 'backup_students_' . $schoolId . '_' . now()->format('Y-m-d_H-i') . '.xlsx';
    $path = 'backups/students/' . $filename;

    // حفظ الملف باستخدام Excel
    $success = Excel::store($export, $path, 'public');

    // التحقق من نجاح التخزين ووجود الملف
    if (!$success || !Storage::disk('public')->exists($path)) {
        throw new \Exception("فشل إنشاء النسخة الاحتياطية: الملف لم يُحفظ بنجاح.");
    }

    // حساب الحجم من نفس الـ disk
    $fileSize = Storage::disk('public')->size($path);

    // عدد الطلاب الحاليين
    $studentsCount = Student::where('school_id', $schoolId)->count();

    // إنشاء سجل النسخة الاحتياطية في قاعدة البيانات
    $backup = StudentBackup::create([
        'school_id' => $schoolId,
        'backup_name' => $backupName,
        'file_path' => $path,
        'file_size' => $fileSize,
        'students_count' => $studentsCount,
        'notes' => $notes,
        'created_by' => auth()->id(),
        'created_at' => now(),
    ]);

    // تسجيل العملية في اللوج
    Log::info('Student backup created', [
        'backup_id' => $backup->id,
        'school_id' => $schoolId,
        'students_count' => $studentsCount,
        'file_size' => $fileSize
    ]);

    // إرجاع البيانات النهائية للواجهة الأمامية أو API
    return [
        'backup_id' => $backup->id,
        'backup_name' => $backupName,
        'students_count' => $studentsCount,
        'file_size' => $this->formatFileSize($fileSize),
        'created_at' => now()->toDateTimeString()
    ];
}

    /**
     * تنسيق حجم الملف
     */
    private function formatFileSize($bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}