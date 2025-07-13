<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class GuardianController extends Controller
{
    /**
     * عرض قائمة أولياء الأمور مع إمكانية التصفية
     */
    public function index(Request $request): JsonResponse
    {
        $query = Guardian::with(['school', 'students', 'supervisors']);

        // تصفية حسب المدرسة
        if ($request->has('school_id')) {
            $query->bySchool($request->school_id);
        }

        // تصفية حسب الحالة
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // تصفية حسب صلة القرابة
        if ($request->has('relationship')) {
            $query->where('relationship', $request->relationship);
        }

        // تصفية حسب المساعد
        if ($request->has('supervisor_id')) {
            $query->whereHas('supervisors', function ($q) use ($request) {
                $q->where('supervisor_id', $request->supervisor_id);
            });
        }

        // البحث
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name_ar', 'like', "%{$request->search}%")
                  ->orWhere('name_en', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('national_id', 'like', "%{$request->search}%");
            });
        }

        // ترتيب النتائج
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        // الصفحات أو كل النتائج
        if ($request->has('per_page')) {
            $guardians = $query->paginate($request->per_page);
        } else {
            $guardians = $query->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب قائمة أولياء الأمور بنجاح',
            'data' => $guardians,
        ]);
    }

    /**
     * عرض تفاصيل ولي أمر محدد
     */
    public function show(Guardian $guardian): JsonResponse
    {
        $guardian->load([
            'school',
            'students' => function ($query) {
                $query->with('supervisors');
            },
            'supervisors' => function ($query) {
                $query->with('students');
            }
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم جلب بيانات ولي الأمر بنجاح',
            'data' => $guardian,
        ]);
    }

    /**
     * إنشاء ولي أمر جديد
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'national_id' => 'nullable|string|max:20',
            'relationship' => ['required', Rule::in(['father', 'mother', 'grandfather', 'grandmother', 'uncle', 'aunt', 'other'])],
            'address_ar' => 'nullable|string',
            'address_en' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $guardian = Guardian::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء ولي الأمر بنجاح',
            'data' => $guardian->load('school'),
        ], 201);
    }

    /**
     * تحديث بيانات ولي أمر
     */
    public function update(Request $request, Guardian $guardian): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'sometimes|exists:schools,id',
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'email' => 'nullable|email|max:255',
            'national_id' => 'nullable|string|max:20',
            'relationship' => ['sometimes', Rule::in(['father', 'mother', 'grandfather', 'grandmother', 'uncle', 'aunt', 'other'])],
            'address_ar' => 'nullable|string',
            'address_en' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $guardian->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات ولي الأمر بنجاح',
            'data' => $guardian->load('school'),
        ]);
    }

    /**
     * حذف ولي أمر
     */
    public function destroy(Guardian $guardian): JsonResponse
    {
        // فصل جميع العلاقات قبل الحذف
        $guardian->students()->detach();
        $guardian->supervisors()->detach();

        $guardian->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف ولي الأمر بنجاح',
        ]);
    }

    /**
     * ربط ولي أمر بمساعد
     */
    public function attachSupervisor(Request $request, Guardian $guardian): JsonResponse
    {
        $validated = $request->validate([
            'supervisor_id' => 'required|exists:supervisors,id',
            'notes' => 'nullable|string',
            'is_primary' => 'boolean',
        ]);

        $supervisor = Supervisor::findOrFail($validated['supervisor_id']);

        // التحقق من أن ولي الأمر والمساعد في نفس المدرسة
        if ($guardian->school_id !== $supervisor->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن ربط ولي الأمر بالمساعد لأنهما في مدارس مختلفة',
            ], 422);
        }

        // إذا كان المساعد أساسي، قم بإلغاء تفعيل المساعدين الأساسيين الآخرين
        if ($validated['is_primary'] ?? false) {
            $guardian->supervisors()->updateExistingPivot(
                $guardian->supervisors->pluck('id')->toArray(),
                ['is_primary' => false]
            );
        }

        // ربط أو تحديث المساعد
        if ($guardian->supervisors()->where('supervisor_id', $validated['supervisor_id'])->exists()) {
            $guardian->supervisors()->updateExistingPivot($validated['supervisor_id'], [
                'notes' => $validated['notes'] ?? null,
                'is_primary' => $validated['is_primary'] ?? false,
            ]);
        } else {
            $guardian->supervisors()->attach($validated['supervisor_id'], [
                'notes' => $validated['notes'] ?? null,
                'assigned_date' => now(),
                'is_primary' => $validated['is_primary'] ?? false,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم ربط ولي الأمر بالمساعد بنجاح',
        ]);
    }

    /**
     * فصل ولي أمر عن مساعد
     */
    public function detachSupervisor(Guardian $guardian, Supervisor $supervisor): JsonResponse
    {
        $guardian->supervisors()->detach($supervisor->id);

        return response()->json([
            'success' => true,
            'message' => 'تم فصل ولي الأمر عن المساعد بنجاح',
        ]);
    }

    /**
     * البحث في أولياء الأمور
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'school_id' => 'nullable|exists:schools,id',
        ]);

        $query = Guardian::where(function ($q) use ($request) {
            $q->where('name_ar', 'like', "%{$request->query}%")
              ->orWhere('name_en', 'like', "%{$request->query}%")
              ->orWhere('phone', 'like', "%{$request->query}%")
              ->orWhere('national_id', 'like', "%{$request->query}%");
        });

        if ($request->has('school_id')) {
            $query->bySchool($request->school_id);
        }

        $guardians = $query->with('school', 'students', 'supervisors')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'تم البحث بنجاح',
            'data' => $guardians,
        ]);
    }
}