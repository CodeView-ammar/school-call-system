<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Guardian;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /**
     * عرض قائمة الطلاب مع إمكانية التصفية
     */
    public function index(Request $request): JsonResponse
    {
        $query = Student::with(['school', 'branch', 'guardians', 'supervisors', 'bus']);

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

        // تصفية حسب ولي الأمر
        if ($request->has('guardian_id')) {
            $query->whereHas('guardians', function ($q) use ($request) {
                $q->where('guardian_id', $request->guardian_id);
            });
        }

        // تصفية حسب المساعد
        if ($request->has('supervisor_id')) {
            $query->whereHas('supervisors', function ($q) use ($request) {
                $q->where('supervisor_id', $request->supervisor_id);
            });
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
            $students = $query->paginate($request->per_page);
        } else {
            $students = $query->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب قائمة الطلاب بنجاح',
            'data' => $students,
        ]);
    }

    /**
     * عرض تفاصيل طالب محدد
     */
    public function show(Student $student): JsonResponse
    {
        $student->load([
            'school',
            'branch',
            'bus',
            'guardians' => function ($query) {
                $query->with('supervisors');
            },
            'supervisors' => function ($query) {
                $query->with('guardians');
            }
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم جلب بيانات الطالب بنجاح',
            'data' => $student,
        ]);
    }

    /**
     * إنشاء طالب جديد
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'branch_id' => 'nullable|exists:branches,id',
            'academic_band_id' => 'nullable|integer',
            'code' => 'required|string|unique:students,code',
            'student_number' => 'required|string|unique:students,student_number',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'national_id' => 'nullable|string|max:20',
            'date_of_birth' => 'required|date|before:today',
            'gender' => ['required', Rule::in(['male', 'female'])],
            'nationality' => 'nullable|string|max:100',
            'address_ar' => 'nullable|string',
            'address_en' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'medical_notes' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:20',
            'bus_id' => 'nullable|exists:buses,id',
            'pickup_location' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $student = Student::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الطالب بنجاح',
            'data' => $student->load('school', 'branch'),
        ], 201);
    }

    /**
     * تحديث بيانات طالب
     */
    public function update(Request $request, Student $student): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'sometimes|exists:schools,id',
            'branch_id' => 'nullable|exists:branches,id',
            'academic_band_id' => 'nullable|integer',
            'code' => 'sometimes|string|unique:students,code,' . $student->id,
            'student_number' => 'sometimes|string|unique:students,student_number,' . $student->id,
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'national_id' => 'nullable|string|max:20',
            'date_of_birth' => 'sometimes|date|before:today',
            'gender' => ['sometimes', Rule::in(['male', 'female'])],
            'nationality' => 'nullable|string|max:100',
            'address_ar' => 'nullable|string',
            'address_en' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'medical_notes' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:20',
            'bus_id' => 'nullable|exists:buses,id',
            'pickup_location' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $student->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات الطالب بنجاح',
            'data' => $student->load('school', 'branch'),
        ]);
    }

    /**
     * حذف طالب
     */
    public function destroy(Student $student): JsonResponse
    {
        // فصل جميع العلاقات قبل الحذف
        $student->guardians()->detach();
        $student->supervisors()->detach();

        // حذف الصورة إذا كانت موجودة
        if ($student->photo) {
            Storage::delete($student->photo);
        }

        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الطالب بنجاح',
        ]);
    }

    /**
     * رفع صورة للطالب
     */
    public function uploadPhoto(Request $request, Student $student): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // حذف الصورة القديمة إذا كانت موجودة
        if ($student->photo) {
            Storage::delete($student->photo);
        }

        // رفع الصورة الجديدة
        $path = $request->file('photo')->store('students/photos', 'public');
        $student->update(['photo' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'تم رفع صورة الطالب بنجاح',
            'data' => [
                'photo_path' => $path,
                'photo_url' => Storage::url($path),
            ],
        ]);
    }

    /**
     * ربط طالب بولي أمر
     */
    public function attachGuardian(Request $request, Student $student): JsonResponse
    {
        $validated = $request->validate([
            'guardian_id' => 'required|exists:guardians,id',
            'is_primary' => 'boolean',
        ]);

        $guardian = Guardian::findOrFail($validated['guardian_id']);

        // التحقق من أن الطالب وولي الأمر في نفس المدرسة
        if ($student->school_id !== $guardian->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن ربط الطالب بولي الأمر لأنهما في مدارس مختلفة',
            ], 422);
        }

        // إذا كان ولي الأمر أساسي، قم بإلغاء تفعيل الأولياء الأساسيين الآخرين
        if ($validated['is_primary'] ?? false) {
            $student->guardians()->updateExistingPivot(
                $student->guardians->pluck('id')->toArray(),
                ['is_primary' => false]
            );
        }

        // ربط أو تحديث ولي الأمر
        if ($student->guardians()->where('guardian_id', $validated['guardian_id'])->exists()) {
            $student->guardians()->updateExistingPivot($validated['guardian_id'], [
                'is_primary' => $validated['is_primary'] ?? false
            ]);
        } else {
            $student->guardians()->attach($validated['guardian_id'], [
                'is_primary' => $validated['is_primary'] ?? false
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم ربط الطالب بولي الأمر بنجاح',
        ]);
    }

    /**
     * فصل طالب عن ولي أمر
     */
    public function detachGuardian(Student $student, Guardian $guardian): JsonResponse
    {
        $student->guardians()->detach($guardian->id);

        return response()->json([
            'success' => true,
            'message' => 'تم فصل الطالب عن ولي الأمر بنجاح',
        ]);
    }

    /**
     * البحث في الطلاب
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'school_id' => 'nullable|exists:schools,id',
        ]);

        $query = Student::search($request->query);

        if ($request->has('school_id')) {
            $query->bySchool($request->school_id);
        }

        $students = $query->with('school', 'branch', 'guardians')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'تم البحث بنجاح',
            'data' => $students,
        ]);
    }
}