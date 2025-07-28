<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentCallLog;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\StudentCall;
use Illuminate\Support\Carbon;

class StudentCallLogController extends Controller
{
    /**
     * عرض سجلات حالة نداء الطالب حسب student_call_id
     */
    public function index(Request $request)
    {
         $studentId = $request->query('student_id');

        if (!$studentId) {
            return response()->json([
                'message' => 'يرجى تمرير student_id في الطلب.'
            ], 400);
        }

        $today = Carbon::today()->toDateString();

        // جلب نداء اليوم الحالي
        $studentCall = StudentCall::with([
            'latestLog.changedByUser:id,name',
            'student:id,name', // لو أردت جلب بيانات الطالب
        ])
        ->where('student_id', $studentId)
        ->whereDate('call_cdate', $today)
        ->first();

        if (!$studentCall) {
            return response()->json([
                'message' => 'لا يوجد نداء مسجل لهذا الطالب اليوم.',
            ], 404);
        }

        // جلب السجلات المرتبطة بهذا النداء
        $logs = StudentCallLog::where('student_call_id', $studentCall->call_id)
            ->with('changedByUser:id,name')
            ->orderBy('changed_at', 'desc')
            ->get();

        return response()->json([
            'student_call' => $studentCall,
            'logs' => $logs,
        ]);
    }
public function getTodayCallLog(Request $request)
{
    $studentId = $request->query('student_id');
    
    if (!$studentId) {
        return response()->json([
            'message' => 'يرجى تمرير student_id في الطلب.'
        ], 400);
    }
    // جلب بيانات الطالب
    $student = Student::find($studentId);
    if (!$student) {
        return response()->json([
            'message' => 'الطالب غير موجود.'
        ], 404);
    }
    
    $today = Carbon::today();
    // جلب أحدث نداء للطالب (أو حسب شروطك)
    $latestCall = StudentCall::where('student_id', $studentId)
        ->orderBy('call_cdate', 'desc')
        ->with('branch', 'school', 'user')
        ->whereDate('call_cdate', $today)
        ->first();

    // جلب كل سجلات النداء (logs) الخاصة بالطالب من جدول StudentCallLog
    // عبر الانضمام مع جدول StudentCall حسب student_call_id وفلترة حسب student_id
    $logs = StudentCallLog::whereHas('studentCall', function($q) use ($studentId, $today) {
            $q->where('student_id', $studentId);
            $q->whereDate('call_cdate', $today);
        })
        ->with('changedByUser:id,name')
        ->orderBy('changed_at', 'desc')
        ->get();

    return response()->json([
        'student' => [
            'id' => $student->id,
            'name' => $student->name_ar ?? $student->name_en,
            'student_number' => $student->student_number,
            'school_id' => $student->school_id,
            'branch_id' => $student->branch_id,
        ],
        'current_call' => $latestCall ? [
            'call_id' => $latestCall->call_id,
            'status' => $latestCall->status,
            'caller_type' => $latestCall->caller_type,
            'call_date' => $latestCall->call_cdate,
            'user_id' => $latestCall->user_id,
        ] : null,
        'logs' => $logs,
    ]);
}

public function getStudentCalls(Request $request)
{
    $studentId = $request->query('student_id');

    if (!$studentId) {
        return response()->json([
            'message' => 'يرجى تمرير student_id في الطلب.'
        ], 400);
    }

    $calls = StudentCall::with([
            'student:id,name_ar',
            'school:id,name',
            'branch:id,name',
            'latestLog' => function ($query) {
                $query->orderBy('changed_at', 'desc');
            }
        ])
        ->where('student_id', $studentId)
        ->orderByDesc('call_cdate')
        ->get();

    $result = $calls->map(function ($call) {
        $latestLog = $call->latestLog;
        return [
            'call_id'      => $call->call_id,
            'date'         => optional($call->call_cdate)->format('Y-m-d'),
            'time'         => optional($call->call_cdate)->format('H:i'),
            'status'       => $latestLog?->status ?? $call->status,
            'caller_type'  => $call->caller_type,
            'student_name' => $call->student->name ?? null,
            'school_name'  => $call->school->name ?? null,
            'branch_name'  => $call->branch->name ?? null,
        ];
    });

    return response()->json($result);
}

}
