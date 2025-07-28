<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supervisor;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\User;
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

public function store(Request $request): JsonResponse
{
    // التحقق من صحة البيانات المدخلة
    $validated = $request->validate([
        'school_id' => 'required|exists:schools,id',
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'email' => 'nullable|email|unique:supervisors,email',
        'position' => 'nullable|string|max:255',
        'salary' => 'nullable|numeric|min:0',
        'is_active' => 'boolean',
        'students' => 'nullable|array',
        'students.*.student_id' => 'required|exists:students,id',
        'user_id' => 'required|exists:users,id', // إضافة هذا السطر
    ]);

    // إنشاء مستخدم جديد
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'phone' => $validated['phone'],
        'user_type' => 'assistant', // تعيين نوع المستخدم
        'password' => bcrypt("admin123"), // تعيين كلمة مرور مؤقتة
        "school_id"=>$validated['school_id']
    ]);

    // إنشاء مساعد جديد
    $supervisor = Supervisor::create(array_merge($validated, ['user_id' => $user->id]));

    // ربط الطلاب بالمساعد
    if (isset($validated['students'])) {
        $studentIds = array_column($validated['students'], 'student_id');
        $supervisor->students()->sync($studentIds);
    }
    if (isset($validated['user_id'])) {
        $guardian = Guardian::where('user_id', $validated['user_id'])->first();

        if ($guardian) {
            $supervisor->guardians()->sync([$guardian->id]);
        }
    }
    return response()->json([
        'success' => true,
        'message' => 'تم إنشاء المساعد بنجاح',
        'data' => $supervisor->load('school'),
    ], 201);
}
    /**
     * تحديث بيانات مساعد
     */
    public function update(Request $request, Supervisor $supervisor): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'sometimes|exists:schools,id',

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
        
        // حذف المستخدم المرتبط بالمساعد
        if ($supervisor->user_id) {
            $user = User::find($supervisor->user_id);
            if ($user) {
                $user->delete();
            }
        }

        // حذف المساعد
        $supervisor->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المساعد والمستخدم المرتبط به بنجاح',
        ]);
    }

    /**
     * تبديل حالة تفعيل المساعد
     */
    public function toggleStatus($id): JsonResponse
    {
        
        // استرجاع المساعد باستخدام الـ ID
        $supervisor = Supervisor::findOrFail($id);
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
    /**
 * عرض جميع المساعدين مع الطلاب المرتبطين بهم
 */
public function listWithStudents(Request $request): JsonResponse
{
        $request->validate([
        'school_id' => 'required|exists:schools,id',
        'user_id' => 'required|exists:users,id', // ✅ طلب user_id بدل guardian_id
        ]);

    $query = Supervisor::with(['students' => function ($q) {
        $q->select('students.id', 'students.name_ar', 'students.school_id');
    }]);

    // تصفية حسب المدرسة إن وجدت
    if ($request->has('school_id')) {
        $query->where('school_id', $request->school_id);
    }
    if ($request->has('user_id')) {
        $query->where('user_id', $request->user_id);
    }
    dd($query->get());
    $supervisors = $query->get();
    
    $result = $supervisors->map(function ($supervisor) {
        return [
            'id' => $supervisor->id,
            'name' => $supervisor->name_ar ?? $supervisor->name_en,
            'school_id' => $supervisor->school_id,
            
            'students' => $supervisor->students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'branch_id' => $student->branch_id,
                    'class_id' => $student->class_id,
                ];
            }),
        ];
    });

    return response()->json([
        'success' => true,
        'message' => 'تم جلب قائمة المساعدين مع الطلاب',
        'data' => $result,
    ]);
}
public function getSupervisorsWithGuardiansAndStudents(Request $request)
{
  $query = Supervisor::with(['students' => function ($studentQuery) use ($request) {
        // $studentQuery->with('guardians');

        // فلترة الطلاب المرتبطين بولي الأمر ذو user_id
        if ($request->has('user_id')) {
            $studentQuery->whereHas('guardians', function ($guardianQuery) use ($request) {
                $guardianQuery->where('user_id', $request->user_id);
            });
        }
    }]);

    // فلترة حسب المدرسة
    if ($request->has('school_id')) {
        $query->where('school_id', $request->school_id);
    }

    // فلترة حسب الحالة (نشط)
    if ($request->has('is_active')) {
        $query->where('is_active', $request->boolean('is_active'));
    }

    // تنفيذ الاستعلام
    $supervisors = $query->get();

    // إزالة المساعدين الذين ليس لديهم طلاب بعد الفلترة
    $supervisors = $supervisors->filter(function ($supervisor) {
        return $supervisor->students;
    })->values();

    return response()->json([
        'success' => true,
        'message' => 'تم جلب المساعدين مع الطلاب المرتبطين بولي الأمر',
        'data' => $supervisors,
    ]);
}


}