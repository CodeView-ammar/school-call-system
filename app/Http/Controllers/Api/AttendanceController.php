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
    
    public function store(Request $request)
{
    // التحقق من صحة البيانات
    $validator = Validator::make($request->all(), [
        'student_id' => 'required|integer|exists:students,id',
        'school_id' => 'required|integer|exists:schools,id',
        'branch_id' => 'required|integer|exists:branches,id',
        'grade_class_id' => 'required|integer|exists:grade_classes,id',
        'attendance_date' => 'required|date',
        'status' => 'required|string|in:present,absent,late',
        'check_in_time' => 'nullable|date_format:H:i',
        'check_out_time' => 'nullable|date_format:H:i',
        'notes' => 'nullable|string',
        'recorded_by' => 'required|integer|exists:users,id',
        'user_id' => 'required|integer|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'خطأ في التحقق من البيانات.',
            'errors' => $validator->errors()
        ], 422);
    }

    // التحقق مما إذا كان الحضور مسجل مسبقًا لهذا الطالب في نفس اليوم
    $attendance = Attendance::where('student_id', $request->student_id)
        ->whereDate('attendance_date', $request->attendance_date)
        ->first();

    if ($attendance) {
        // تحديث السجل الموجود
        $attendance->update([
            'status' => $request->status,
            'check_in_time' => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
            'notes' => $request->notes,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الحضور بنجاح.',
            'data' => $attendance,
        ], 200);
    } else {
        // إنشاء سجل حضور جديد
        $attendance = Attendance::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الحضور بنجاح.',
            'data' => $attendance,
        ], 201);
    }
}

    // استرجاع الحضور للطالب
    public function show($studentId)
    {
        $attendances = Attendance::where('student_id', $studentId)->get();
        return response()->json($attendances);
    }

    // تحديث الحضور
    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        
        $attendance->update($request->all());

        return response()->json($attendance);
    }
}