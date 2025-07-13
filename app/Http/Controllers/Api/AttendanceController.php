<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * الحصول على معرف المدرسة من المستخدم أو الطلب
     */
    private function getSchoolId(Request $request)
    {
        $user = $request->user();
        
        // Super Admin يمكنه تحديد المدرسة
        if ($user->user_type === 'super_admin' && $request->has('school_id')) {
            return $request->school_id;
        }
        
        // باقي المستخدمين مقيدون بمدرستهم
        return $user->school_id;
    }
    /**
     * عرض قائمة الحضور
     */
    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId($request);
        
        $query = Attendance::with(['student'])->whereHas('student', function($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        });

        // فلترة حسب الطالب
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // فلترة حسب التاريخ
        if ($request->has('date')) {
            $query->whereDate('attendance_date', $request->date);
        }

        // فلترة حسب فترة زمنية
        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('attendance_date', [$request->from_date, $request->to_date]);
        }

        // فلترة حسب نوع الحضور
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // ترتيب النتائج
        $query->orderBy('attendance_date', 'desc');

        // التصفح
        $perPage = min($request->get('per_page', 15), 100);
        $attendances = $query->paginate($perPage);

        return response()->json([
            'data' => $attendances->items(),
            'meta' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total(),
            ]
        ]);
    }

    /**
     * تسجيل حضور جديد
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:present,absent,late,excused',
            'check_in_time' => 'nullable|date_format:H:i:s',
            'check_out_time' => 'nullable|date_format:H:i:s',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $validator->errors()
            ], 422);
        }

        // التحقق من عدم وجود سجل حضور مسبق لنفس الطالب في نفس اليوم
        $existingAttendance = Attendance::where('student_id', $request->student_id)
            ->whereDate('attendance_date', $request->attendance_date)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'message' => 'يوجد سجل حضور مسبق لهذا الطالب في نفس اليوم'
            ], 422);
        }

        $attendance = Attendance::create($request->all());
        $attendance->load('student');

        return response()->json([
            'message' => 'تم تسجيل الحضور بنجاح',
            'data' => $attendance
        ], 201);
    }

    /**
     * عرض سجل حضور محدد
     */
    public function show($id)
    {
        $attendance = Attendance::with('student')->findOrFail($id);

        return response()->json([
            'data' => $attendance
        ]);
    }

    /**
     * تحديث سجل حضور
     */
    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'student_id' => 'sometimes|exists:students,id',
            'attendance_date' => 'sometimes|date',
            'status' => 'sometimes|in:present,absent,late,excused',
            'check_in_time' => 'nullable|date_format:H:i:s',
            'check_out_time' => 'nullable|date_format:H:i:s',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $validator->errors()
            ], 422);
        }

        $attendance->update($request->all());
        $attendance->load('student');

        return response()->json([
            'message' => 'تم تحديث سجل الحضور بنجاح',
            'data' => $attendance
        ]);
    }

    /**
     * حذف سجل حضور
     */
    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return response()->json([
            'message' => 'تم حذف سجل الحضور بنجاح'
        ]);
    }

    /**
     * تسجيل حضور متعدد الطلاب
     */
    public function bulkStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attendance_date' => 'required|date',
            'students' => 'required|array',
            'students.*.student_id' => 'required|exists:students,id',
            'students.*.status' => 'required|in:present,absent,late,excused',
            'students.*.check_in_time' => 'nullable|date_format:H:i:s',
            'students.*.check_out_time' => 'nullable|date_format:H:i:s',
            'students.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $validator->errors()
            ], 422);
        }

        $attendances = [];
        $errors = [];

        foreach ($request->students as $studentData) {
            // التحقق من عدم وجود سجل حضور مسبق
            $existingAttendance = Attendance::where('student_id', $studentData['student_id'])
                ->whereDate('attendance_date', $request->attendance_date)
                ->first();

            if ($existingAttendance) {
                $student = Student::find($studentData['student_id']);
                $errors[] = "الطالب {$student->name_ar} لديه سجل حضور مسبق";
                continue;
            }

            $attendanceData = array_merge($studentData, [
                'attendance_date' => $request->attendance_date
            ]);

            $attendance = Attendance::create($attendanceData);
            $attendances[] = $attendance->load('student');
        }

        return response()->json([
            'message' => 'تم تسجيل الحضور للطلاب بنجاح',
            'data' => $attendances,
            'errors' => $errors
        ], 201);
    }

    /**
     * إحصائيات الحضور
     */
    public function statistics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'student_id' => 'nullable|exists:students,id',
            'school_id' => 'nullable|exists:schools,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Attendance::whereBetween('attendance_date', [$request->from_date, $request->to_date]);

        // فلترة حسب الطالب
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // فلترة حسب المدرسة
        if ($request->has('school_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        $statistics = [
            'total_days' => $query->count(),
            'present_days' => $query->where('status', 'present')->count(),
            'absent_days' => $query->where('status', 'absent')->count(),
            'late_days' => $query->where('status', 'late')->count(),
            'excused_days' => $query->where('status', 'excused')->count(),
        ];

        $statistics['attendance_rate'] = $statistics['total_days'] > 0 
            ? round(($statistics['present_days'] + $statistics['late_days']) / $statistics['total_days'] * 100, 2)
            : 0;

        return response()->json([
            'data' => $statistics
        ]);
    }
}