<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supervisor;
use App\Models\Student;
use App\Models\Guardian;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class SupervisorController extends Controller
{
    /**
     * عرض قائمة المساعدين مع إمكانية التصفية
     */
    public function index(Request $request): JsonResponse
    {
        $query = Supervisor::with(['school', 'branch', 'students', 'guardians']);

        // تصفية حسب المدرسة
        if ($request->has('school_id')) {
            $query->bySchool($request->school_id);
        }

        // تصفية حسب الفرع
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // تصفية حسب الجنس
        if ($request->has('gender')) {
            $query->byGender($request->gender);
        }

        // تصفية حسب الحالة
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        

        // البحث
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // ترتيب النتائج
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        // الصفحات أو كل النتائج
        if ($request->has('per_page')) {
            $supervisors = $query->paginate($request->per_page);
        } else {
            $supervisors = $query->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب قائمة المساعدين بنجاح',
            'data' => $supervisors,
        ]);
    }

    /**
     * عرض تفاصيل مساعد محدد
     */
    public function show(Supervisor $supervisor): JsonResponse
    {
        $supervisor->load([
            'school',
            'branch',
            'user',
            'students' => function ($query) {
                $query->with('guardians');
            },
            'guardians' => function ($query) {
                $query->with('students');
            },
            'buses'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم جلب بيانات المساعد بنجاح',
            'data' => $supervisor,
        ]);
    }

    /**
     * إنشاء مساعد جديد
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'branch_id' => 'nullable|exists:branches,id',
            'employee_id' => 'required|string|unique:supervisors,employee_id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|unique:supervisors,email',
            'national_id' => 'nullable|string|max:20|unique:supervisors,national_id',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:20',
            'hire_date' => 'required|date',

            'position' => 'nullable|string|max:255',
            'salary' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $supervisor = Supervisor::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء المساعد بنجاح',
            'data' => $supervisor->load('school', 'branch'),
        ], 201);
    }

    /**
     * تحديث بيانات مساعد
     */
    public function update(Request $request, Supervisor $supervisor): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'sometimes|exists:schools,id',
            'branch_id' => 'nullable|exists:branches,id',
            'employee_id' => 'sometimes|string|unique:supervisors,employee_id,' . $supervisor->id,
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'email' => 'nullable|email|unique:supervisors,email,' . $supervisor->id,
            'national_id' => 'nullable|string|max:20|unique:supervisors,national_id,' . $supervisor->id,
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:20',
            'hire_date' => 'sometimes|date',
            'position' => 'nullable|string|max:255',
            'salary' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $supervisor->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات المساعد بنجاح',
            'data' => $supervisor->load('school', 'branch'),
        ]);
    }

    /**
     * حذف مساعد
     */
    public function destroy(Supervisor $supervisor): JsonResponse
    {
        // فصل جميع العلاقات قبل الحذف
        $supervisor->students()->detach();
        $supervisor->guardians()->detach();
        $supervisor->buses()->detach();

        $supervisor->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المساعد بنجاح',
        ]);
    }

    /**
     * تبديل حالة تفعيل المساعد
     */
    public function toggleStatus(Supervisor $supervisor): JsonResponse
    {
        $supervisor->toggleStatus();

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير حالة المساعد بنجاح',
            'data' => $supervisor,
        ]);
    }

    /**
     * ربط مساعد بطالب
     */
    public function attachStudent(Request $request, Supervisor $supervisor): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'notes' => 'nullable|string',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        // التحقق من أن الطالب والمساعد في نفس المدرسة
        if ($student->school_id !== $supervisor->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن ربط الطالب بالمساعد لأنهما في مدارس مختلفة',
            ], 422);
        }

        $supervisor->attachStudent($validated['student_id'], $validated['notes'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'تم ربط الطالب بالمساعد بنجاح',
        ]);
    }

    /**
     * فصل مساعد عن طالب
     */
    public function detachStudent(Supervisor $supervisor, Student $student): JsonResponse
    {
        $supervisor->detachStudent($student->id);

        return response()->json([
            'success' => true,
            'message' => 'تم فصل الطالب عن المساعد بنجاح',
        ]);
    }

    /**
     * ربط مساعد بولي أمر
     */
    public function attachGuardian(Request $request, Supervisor $supervisor): JsonResponse
    {
        $validated = $request->validate([
            'guardian_id' => 'required|exists:guardians,id',
            'notes' => 'nullable|string',
            'is_primary' => 'boolean',
        ]);

        $guardian = Guardian::findOrFail($validated['guardian_id']);

        // التحقق من أن ولي الأمر والمساعد في نفس المدرسة
        if ($guardian->school_id !== $supervisor->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن ربط ولي الأمر بالمساعد لأنهما في مدارس مختلفة',
            ], 422);
        }

        $supervisor->attachGuardian(
            $validated['guardian_id'], 
            $validated['notes'] ?? null,
            $validated['is_primary'] ?? false
        );

        return response()->json([
            'success' => true,
            'message' => 'تم ربط ولي الأمر بالمساعد بنجاح',
        ]);
    }

    /**
     * فصل مساعد عن ولي أمر
     */
    public function detachGuardian(Supervisor $supervisor, Guardian $guardian): JsonResponse
    {
        $supervisor->detachGuardian($guardian->id);

        return response()->json([
            'success' => true,
            'message' => 'تم فصل ولي الأمر عن المساعد بنجاح',
        ]);
    }

    /**
     * الحصول على إحصائيات المساعد
     */
    public function statistics(Supervisor $supervisor): JsonResponse
    {
        $stats = [
            'students_count' => $supervisor->students()->count(),
            'guardians_count' => $supervisor->guardians()->count(),
            'active_students_count' => $supervisor->students()->where('is_active', true)->count(),
            'work_years' => $supervisor->work_years,
            'recent_assignments' => [
                'students' => $supervisor->students()
                    ->wherePivot('assigned_date', '>=', now()->subMonth())
                    ->count(),
                'guardians' => $supervisor->guardians()
                    ->wherePivot('assigned_date', '>=', now()->subMonth())
                    ->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'تم جلب إحصائيات المساعد بنجاح',
            'data' => $stats,
        ]);
    }

    /**
     * البحث في المساعدين
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'school_id' => 'nullable|exists:schools,id',
        ]);

        $query = Supervisor::search($request->query);

        if ($request->has('school_id')) {
            $query->bySchool($request->school_id);
        }

        $supervisors = $query->with('school', 'branch')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'تم البحث بنجاح',
            'data' => $supervisors,
        ]);
    }
}