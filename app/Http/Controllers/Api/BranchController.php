<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BranchController extends Controller
{
    /**
     * عرض قائمة الفروع
     */
    public function index(Request $request): JsonResponse
    {
        $query = Branch::with(['school', 'students', 'supervisors']);

        // تصفية حسب معرف المدرسة
        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // تصفية حسب الحالة
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // البحث
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name_ar', 'like', "%{$request->search}%")
                  ->orWhere('name_en', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('address_ar', 'like', "%{$request->search}%")
                  ->orWhere('address_en', 'like', "%{$request->search}%");
            });
        }

        // ترتيب النتائج
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        // الصفحات أو كل النتائج
        if ($request->has('per_page')) {
            $branches = $query->paginate($request->per_page);
        } else {
            $branches = $query->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب قائمة الفروع بنجاح',
            'data' => $branches,
        ]);
    }

    /**
     * جلب فروع مدرسة محددة
     */
    public function getBySchool(int $schoolId): JsonResponse
    {
        // التحقق من وجود المدرسة
        $school = School::find($schoolId);
        if (!$school) {
            return response()->json([
                'success' => false,
                'message' => 'المدرسة غير موجودة',
            ], 404);
        }

        $branches = Branch::where('school_id', $schoolId)
            ->where('is_active', true)
            ->with(['students' => function($query) {
                $query->where('is_active', true)->count();
            }])
            ->orderBy('name_ar')
            ->get();

        // إضافة إحصائيات لكل فرع
        $branches->each(function ($branch) {
            $branch->students_count = $branch->students()->where('is_active', true)->count();
            $branch->supervisors_count = $branch->supervisors()->where('is_active', true)->count();
            $branch->buses_count = $branch->buses()->where('is_active', true)->count();
        });

        return response()->json([
            'success' => true,
            'message' => 'تم جلب فروع المدرسة بنجاح',
            'data' => $branches,
            'school' => [
                'id' => $school->id,
                'name_ar' => $school->name_ar,
                'name_en' => $school->name_en,
            ]
        ]);
    }

    /**
     * عرض تفاصيل فرع محدد
     */
    public function show(Branch $branch): JsonResponse
    {
        $branch->load([
            'school',
            'students' => function($query) {
                $query->where('is_active', true);
            },
            'supervisors' => function($query) {
                $query->where('is_active', true);
            },
            'buses' => function($query) {
                $query->where('is_active', true);
            },
            'gradeClass'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم جلب بيانات الفرع بنجاح',
            'data' => $branch,
        ]);
    }

    /**
     * إنشاء فرع جديد
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50|unique:branches,code',
            'address_ar' => 'nullable|string',
            'address_en' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
        ]);

        // التحقق من قدرة المدرسة على إضافة فروع جديدة
        $school = School::find($validated['school_id']);
        if (!$school->canAddMoreBranches()) {
            return response()->json([
                'success' => false,
                'message' => 'لقد وصلت المدرسة للحد الأقصى من الفروع',
            ], 400);
        }

        $branch = Branch::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الفرع بنجاح',
            'data' => $branch,
        ], 201);
    }

    /**
     * تحديث بيانات فرع
     */
    public function update(Request $request, Branch $branch): JsonResponse
    {
        $validated = $request->validate([
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50|unique:branches,code,' . $branch->id,
            'address_ar' => 'nullable|string',
            'address_en' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
        ]);

        $branch->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات الفرع بنجاح',
            'data' => $branch,
        ]);
    }

    /**
     * حذف فرع
     */
    public function destroy(Branch $branch): JsonResponse
    {
        // التحقق من عدم وجود طلاب في الفرع
        if ($branch->students()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف الفرع لوجود طلاب مرتبطين به',
            ], 400);
        }

        $branch->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الفرع بنجاح',
        ]);
    }

    /**
     * الحصول على إحصائيات الفرع
     */
    public function statistics(Branch $branch): JsonResponse
    {
        $stats = [
            'students_count' => $branch->students()->count(),
            'active_students_count' => $branch->students()->where('is_active', true)->count(),
            'male_students_count' => $branch->students()->where('gender', 'male')->count(),
            'female_students_count' => $branch->students()->where('gender', 'female')->count(),
            'supervisors_count' => $branch->supervisors()->count(),
            'active_supervisors_count' => $branch->supervisors()->where('is_active', true)->count(),
            'buses_count' => $branch->buses()->count(),
            'active_buses_count' => $branch->buses()->where('is_active', true)->count(),
            'classes_count' => $branch->gradeClass()->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'تم جلب إحصائيات الفرع بنجاح',
            'data' => $stats,
        ]);
    }

    /**
     * الحصول على أسماء الفروع فقط (للاستخدام في القوائم المنسدلة)
     */
    public function getBranchNames(int $schoolId): JsonResponse
    {
        $school = School::find($schoolId);
        if (!$school) {
            return response()->json([
                'success' => false,
                'message' => 'المدرسة غير موجودة',
            ], 404);
        }

        $branches = Branch::where('school_id', $schoolId)
            ->where('is_active', true)
            ->select('id', 'name_ar', 'name_en', 'code')
            ->orderBy('name_ar')
            ->get()
            ->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name_ar,
                    "name_en" => $branch->name_en,
                    'code' => $branch->code,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'تم جلب أسماء الفروع بنجاح',
            'data' => $branches,
        ]);
    }
}
