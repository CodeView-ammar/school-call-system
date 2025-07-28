<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Student;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SchoolController extends Controller
{
    /**
     * عرض قائمة المدارس
     */
    public function index(Request $request): JsonResponse
    {
        $query = School::with(['students', 'supervisors', 'guardians']);

        // تصفية حسب الحالة
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // البحث
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name_ar', 'like', "%{$request->search}%")
                  ->orWhere('name_en', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        // ترتيب النتائج
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        // الصفحات أو كل النتائج
        if ($request->has('per_page')) {
            $schools = $query->paginate($request->per_page);
        } else {
            $schools = $query->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب قائمة المدارس بنجاح',
            'data' => $schools,
        ]);
    }

    /**
     * عرض تفاصيل مدرسة محددة
     */
    public function show(School $school): JsonResponse
    {
        $school->load(['students', 'supervisors', 'guardians', 'buses', 'drivers']);

        return response()->json([
            'success' => true,
            'message' => 'تم جلب بيانات المدرسة بنجاح',
            'data' => $school,
        ]);
    }

    /**
     * إنشاء مدرسة جديدة
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'address_ar' => 'nullable|string',
            'address_en' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
        ]);

        $school = School::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء المدرسة بنجاح',
            'data' => $school,
        ], 201);
    }

    /**
     * تحديث بيانات مدرسة
     */
    public function update(Request $request, School $school): JsonResponse
    {
        $validated = $request->validate([
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'address_ar' => 'nullable|string',
            'address_en' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
        ]);

        $school->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات المدرسة بنجاح',
            'data' => $school,
        ]);
    }

    /**
     * حذف مدرسة
     */
    public function destroy(School $school): JsonResponse
    {
        $school->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المدرسة بنجاح',
        ]);
    }

    /**
     * الحصول على إحصائيات المدرسة
     */
    public function statistics(School $school): JsonResponse
    {
        $stats = [
            'students_count' => $school->students()->count(),
            'active_students_count' => $school->students()->where('is_active', true)->count(),
            'male_students_count' => $school->students()->where('gender', 'male')->count(),
            'female_students_count' => $school->students()->where('gender', 'female')->count(),
            'supervisors_count' => $school->supervisors()->count(),
            'active_supervisors_count' => $school->supervisors()->where('is_active', true)->count(),
            'guardians_count' => $school->guardians()->count(),
            'active_guardians_count' => $school->guardians()->where('is_active', true)->count(),
            'buses_count' => $school->buses()->count(),
            'active_buses_count' => $school->buses()->where('is_active', true)->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'تم جلب إحصائيات المدرسة بنجاح',
            'data' => $stats,
        ]);
    }
    /**
 * جلب الفصول المرتبطة بمدرسة محددة
 */
public function classes(Request $request, School $school): JsonResponse
{
    $query = $school->gradeClasses()->with(['academicBand']);

    // فلترة حسب الفرع إن وُجد
    if ($request->has('branch_id')) {
        $query->where('branch_id', $request->branch_id);
    }

    $classes = $query->get();

    return response()->json([
        'success' => true,
        'message' => 'تم جلب الفصول الدراسية الخاصة بالمدرسة بنجاح',
        'data' => $classes,
    ]);
}

public function studentsByBranch(Request $request, $branchId): JsonResponse
{
    $students = Student::with(['school', 'branch', 'gradeClass'])
        ->where('branch_id', $branchId)
        ->where('grade_class_id', $request->grade_class_id)
        ->where('is_active', true)
        ->where('school_id', $request->school_id)
        ->get();

    return response()->json([
        'success' => true,
        'message' => 'تم جلب الطلاب المرتبطين بالفرع بنجاح',
        'data' => $students,
    ]);
}

}