<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\StudentCall;
use App\Models\StudentCallLog;
use Illuminate\Http\Request;
use App\Http\Resources\StudentCallResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class StudentCallController extends Controller
{
    public function index(Request $request)
    {
        $query = StudentCall::with(['student', 'school','student.gradeClass', 'branch']);
        // تصفية حسب المدرسة
        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }
        // تصفية حسب الفرع
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        
        // تصفية حسب الفصل
        if ($request->has('grade_class_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('grade_class_id', $request->grade_class_id);
            });
        }
        
        if($request->has('call_period'))
            $query->where('call_period', $request->call_period);
            // dd($request->call_period);
          // فلترة حسب الطالب
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        // تصفية حسب التاريخ
        if ($request->has('date')) {
            $query->whereDate('call_cdate', $request->date);
        }

        // تصفية حسب الحالة
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // البحث النصي
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->whereHas('student', function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                ->orWhere('student_code', 'like', "%{$searchTerm}%");
            });
        }
        
        $calls = $query->orderBy('call_cdate', 'desc')->get();

        return response()->json($calls);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'student_id' => 'required|integer|exists:students,id',
                'school_id' => 'required|integer|exists:schools,id',
                'branch_id' => 'required|integer|exists:branches,id',
                'user_id' => 'required|integer|exists:users,id',
                'call_cdate' => 'required|date',
                'status' => 'required|string',
                'caller_type' => 'required|string|in:guardian,assistant,bus,supervisor',
                'call_level' => 'nullable|string|in:normal,urgent',
                'notes' => 'nullable|string',
                "call_period"=> 'nullable|string',
            ]);
            DB::beginTransaction();
            
            // استخراج تاريخ فقط (بدون وقت) من call_cdate
            $callDate = Carbon::parse($validatedData['call_cdate'])->format('Y-m-d');
            
            if(!$request->has('call_period'))    
            {
                    // dd($request);
                $validatedData['call_period'] = 'evening';
            }
            // تحقق إذا كان هناك نداء لنفس الطالب في نفس اليوم
            $existingCall = StudentCall::where('student_id', $validatedData['student_id'])
            ->whereDate('call_cdate', $callDate)
            ->where("call_period",$validatedData['call_period'])
            ->first();
            
            if ($existingCall) {
                DB::rollBack();
                return response()->json([
                    'success' => true,
                    'message' => 'تم العثور على نداء موجود لهذا الطالب اليوم',
                    'data' => [
                        'call_id' => $existingCall->call_id,
                        'status' => $existingCall->status,
                        'existing' => true
                        ]
                    ], 200);
                }
            // إنشاء نداء جديد
            $studentCall = StudentCall::create($validatedData);

            // حفظ سجل الحالة في جدول student_calls_log
            StudentCallLog::create([
                'student_call_id' => $studentCall->call_id,
                'status' => $validatedData['status'],
                'changed_at' => now(),
                'changed_by_user_id' => $validatedData['user_id'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء نداء الطالب بنجاح',
                'data' => [
                    'call_id' => $studentCall->call_id,
                    'status' => $studentCall->status,
                    'existing' => false
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطأ في التحقق من البيانات',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in StudentCallController@store: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'خطأ في الخادم',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(StudentCall $studentCall)
    {
        return new StudentCallResource($studentCall->load(['student', 'school', 'user', 'branch']));
    }

    public function update(Request $request, StudentCall $studentCall)
    {
        try {
            $data = $request->validate([
                'student_id' => 'sometimes|exists:students,id',
                'school_id' => 'sometimes|exists:schools,id',
                'branch_id' => 'sometimes|exists:branches,id',
                'user_id' => 'sometimes|exists:users,id',
                'call_cdate' => 'sometimes|date',
                'call_edate' => 'nullable|date',
                'status' => 'required|string|in:prepare,leave,with_teacher,to_gate,received,canceled',
                'caller_type' => 'sometimes|in:guardian,assistant,bus,supervisor',
                'call_level' => 'sometimes|in:normal,urgent',
                'notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $studentCall->update($data);

            // حفظ سجل التغيير
            if (isset($data['status'])) {
                StudentCallLog::create([
                    'student_call_id' => $studentCall->call_id,
                    'status' => $data['status'],
                    'changed_at' => now(),
                    'changed_by_user_id' => $data['user_id'] ?? auth()->id(),
                ]);
            }

            DB::commit();

            return new StudentCallResource($studentCall->fresh()->load(['student', 'school', 'user', 'branch']));

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تحديث النداء',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            // التحقق من صحة البيانات
            $validatedData = $request->validate([
                'status' => 'required|string',
                'user_id' => 'nullable|integer|exists:users,id',
                'notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            // جلب النداء
            $call = StudentCall::findOrFail($id);

            // تحديث الحالة
            $call->status = $validatedData['status'];
            if (isset($validatedData['notes'])) {
                $call->notes = $validatedData['notes'];
            }
            $call->save();

            // تحديد المستخدم الذي غيّر الحالة
            $changedByUserId = $validatedData['user_id'] ?? auth()->id();

            // حفظ السجل في جدول logs
            StudentCallLog::create([
                'student_call_id' => $id,
                'status' => $call->status,
                'changed_at' => now(),
                'changed_by_user_id' => $changedByUserId,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الحالة بنجاح',
                'data' => [
                    'call_id' => $call->call_id,
                    'status' => $call->status,
                    'updated_at' => $call->updated_at,
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطأ في التحقق من البيانات',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'النداء غير موجود',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update Status Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ غير متوقع أثناء تحديث الحالة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(StudentCall $studentCall)
    {
        try {
            DB::beginTransaction();

            // حذف سجلات الـ logs المرتبطة
            StudentCallLog::where('student_call_id', $studentCall->call_id)->delete();

            // حذف النداء
            $studentCall->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف النداء بنجاح'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطأ في حذف النداء',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function todayLatestByStudent($studentId,$call_period): JsonResponse
    {
        try {
            $today = Carbon::today();

            // ابحث عن آخر نداء لهذا الطالب في تاريخ اليوم
            $call = StudentCall::where('student_id', $studentId)
                ->where("call_period",$call_period)
                ->whereDate('call_cdate', $today)
                ->with(['student', 'school', 'branch'])
                ->latest('call_cdate')
                ->first();

            if (!$call) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد نداء لهذا الطالب اليوم',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم العثور على النداء',
                'data' => [
                    'call_id' => $call->call_id,
                    'status' => $call->status,
                    'call_cdate' => $call->call_cdate,
                    'caller_type' => $call->caller_type,
                    'call_level' => $call->call_level,
                    'notes' => $call->notes,
                    'student_name' => $call->student?->name,
                    'school_name' => $call->school?->name,
                    'branch_name' => $call->branch?->name,
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error in todayLatestByStudent: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'خطأ في جلب بيانات النداء',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function statistics(Request $request): JsonResponse
    {
        try {
            $query = StudentCall::query();

            // تطبيق المرشحات
            if ($request->has('school_id')) {
                $query->where('school_id', $request->school_id);
            }

            if ($request->has('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }

            if ($request->has('class_id')) {
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('class_id', $request->class_id);
                });
            }

            if ($request->has('date')) {
                $query->whereDate('call_cdate', $request->date);
            }

            // حساب الإحصائيات
            $totalCalls = $query->count();
            $prepareCalls = $query->clone()->where('status', 'prepare')->count();
            $leaveCalls = $query->clone()->where('status', 'leave')->count();
            $withTeacherCalls = $query->clone()->where('status', 'with_teacher')->count();
            $toGateCalls = $query->clone()->where('status', 'to_gate')->count();
            $receivedCalls = $query->clone()->where('status', 'received')->count();
            $canceledCalls = $query->clone()->where('status', 'canceled')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_calls' => $totalCalls,
                    'prepare_calls' => $prepareCalls,
                    'leave_calls' => $leaveCalls,
                    'with_teacher_calls' => $withTeacherCalls,
                    'to_gate_calls' => $toGateCalls,
                    'received_calls' => $receivedCalls,
                    'canceled_calls' => $canceledCalls,
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error in statistics: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'خطأ في جلب الإحصائيات',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
