<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\Guardian;
use App\Models\Branch;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BusController extends Controller
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
     * عرض قائمة الحافلات
     */
    public function index(Request $request)
    {
        $query = Bus::with(['driver', 'supervisor', 'students', 'route']);

        // فلترة حسب المدرسة (إجباري)
        $schoolId = $this->getSchoolId($request);
        $query->where('school_id', $schoolId);

        // فلترة حسب النشاط
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // البحث
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bus_number', 'like', "%{$search}%")
                  ->orWhere('license_plate', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        $buses = $query->get();

        return response()->json([
            'data' => $buses
        ]);
    }

    /**
     * إنشاء حافلة جديدة
     */
    public function store(Request $request)
    {
        // تعيين معرف المدرسة تلقائياً
        $schoolId = $this->getSchoolId($request);
        $request->merge(['school_id' => $schoolId]);

        $validator = Validator::make($request->all(), [
            'school_id' => 'required|exists:schools,id',
            'bus_number' => 'required|string|unique:buses,bus_number',
            'license_plate' => 'required|string|unique:buses,license_plate',
            'model' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1990|max:2030',
            'capacity' => 'required|integer|min:1|max:100',
            'driver_id' => 'nullable|exists:drivers,id',
            'supervisor_id' => 'nullable|exists:supervisors,id',
            'route_id' => 'nullable|exists:bus_routes,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $validator->errors()
            ], 422);
        }

        $bus = Bus::create($request->all());
        $bus->load(['driver', 'supervisor', 'students', 'route']);

        return response()->json([
            'message' => 'تم إنشاء الحافلة بنجاح',
            'data' => $bus
        ], 201);
    }

    /**
     * عرض حافلة محددة
     */
    public function show($id)
    {
        $bus = Bus::with(['driver', 'supervisor', 'students', 'route'])->findOrFail($id);

        return response()->json([
            'data' => $bus
        ]);
    }

    /**
     * تحديث حافلة
     */
    public function update(Request $request, $id)
    {
        $bus = Bus::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'school_id' => 'sometimes|exists:schools,id',
            'bus_number' => 'sometimes|string|unique:buses,bus_number,' . $id,
            'license_plate' => 'sometimes|string|unique:buses,license_plate,' . $id,
            'model' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1990|max:2030',
            'capacity' => 'sometimes|integer|min:1|max:100',
            'driver_id' => 'nullable|exists:drivers,id',
            'supervisor_id' => 'nullable|exists:supervisors,id',
            'route_id' => 'nullable|exists:bus_routes,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $validator->errors()
            ], 422);
        }

        $bus->update($request->all());
        $bus->load(['driver', 'supervisor', 'students', 'route']);

        return response()->json([
            'message' => 'تم تحديث الحافلة بنجاح',
            'data' => $bus
        ]);
    }

    /**
     * حذف حافلة
     */
    public function destroy($id)
    {
        $bus = Bus::findOrFail($id);
        
        // التحقق من وجود طلاب مرتبطين
        if ($bus->students()->count() > 0) {
            return response()->json([
                'message' => 'لا يمكن حذف الحافلة لأنها مرتبطة بطلاب'
            ], 422);
        }

        $bus->delete();

        return response()->json([
            'message' => 'تم حذف الحافلة بنجاح'
        ]);
    }

    public function getBusesByGuardianAndSchool(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'user_id' => 'required|exists:users,id', // ✅ طلب user_id بدل guardian_id
        ]);

        // ✅ جلب Guardian حسب user_id و school_id
        $guardian = \App\Models\Guardian::with(['students.bus' => function ($query) use ($request) {
            $query->where('school_id', $request->school_id);
        }])
        ->where('user_id', $request->user_id)
        ->where('school_id', $request->school_id)
        ->first();

        if (!$guardian) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على بيانات ولي الأمر.',
            ], 404);
        }

        // ✅ استخراج الباصات من الطلاب المرتبطين
        $buses = $guardian->students
            ->filter(fn($student) => $student->bus && $student->bus->school_id == $request->school_id)
            ->pluck('bus')
            ->unique('id')
            ->values()
            ->map(function ($bus) {
                return [
                    'id' => $bus->id,
                    'number' => $bus->number,
                    'plate_number' => $bus->plate_number,
                    'capacity' => $bus->capacity,
                    'available_seats' => $bus->available_seats,
                    'school' => optional($bus->school)->name_ar,
                    'branch' => optional($bus->branch)->name_ar,
                    'driver' => optional($bus->driver)->name,
                    'supervisor' => optional($bus->supervisor)->name,
                ];
            });

        return response()->json([
            'success' => true,
            'buses' => $buses,
        ]);
    }

public function getStudentsWithBusesByGuardianAndSchool(Request $request)
{
    $request->validate([
        'school_id' => 'required|exists:schools,id',
        'user_id' => 'required|exists:users,id',
    ]);

    // جلب ولي الأمر مع الطلاب وباص كل طالب في المدرسة المحددة
    $guardian = \App\Models\Guardian::with(['students.bus' => function ($query) use ($request) {
        $query->where('school_id', $request->school_id)
              ->with(['branch', 'driver']); // تأكد من جلب الفرع والسائق
    }])
    ->where('user_id', $request->user_id)
    ->where('school_id', $request->school_id)
    ->first();

    if (!$guardian) {
        return response()->json([
            'success' => false,
            'message' => 'لم يتم العثور على بيانات ولي الأمر.',
        ], 404);
    }

    // جلب الطلاب مع معلومات الباص الخاص بكل طالب (إن وجد)
    $students = $guardian->students->map(function ($student) use ($request) {
        $bus = $student->bus && $student->bus->school_id == $request->school_id ? $student->bus : null;

        return [
            'student_id' => $student->id,
            'name_ar' => $student->name_ar,
            'latitude' => $student->latitude,
            'longitude' => $student->longitude,
            'bus' => $bus ? [
                'id' => $bus->id,
                'number' => $bus->number,
                'plate_number' => $bus->plate_number,
                'capacity' => $bus->capacity,
                'available_seats' => $bus->available_seats,
                'school' => optional($bus->school)->name_ar,
                'branch' => optional($bus->branch)->name_ar,
                'branch_latitude' => optional($bus->branch)->latitude,
                'branch_longitude' => optional($bus->branch)->longitude,
                'driver' => $bus->driver ? [
                    'id' => optional($bus->supervisor)->name,
                    'name' => $bus->driver->name,
                    'phone' => $bus->driver->phone,
                ] : null,
                'supervisor' => optional($bus->supervisor)->name,
            ] : null,
        ];
    });

    return response()->json([
        'success' => true,
        'students' => $students,
    ]);
}
    public function getDriverBranches(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'school_id' => 'required|exists:schools,id',
        ]);

        // نجلب الفروع المرتبطة بباصات هذا السائق وهذه المدرسة
        $branches = Branch::whereHas('buses', function ($query) use ($validated) {
            $query->where('driver_id', $validated['user_id'])
                ->where('school_id', $validated['school_id'])
                ->whereNotNull('branch_id');
        })
        ->select('id', 'name_ar', 'name_en')
        ->distinct()
        ->get();

        return response()->json([
            'success' => true,
            'branches' => $branches,
        ], 200, [], JSON_UNESCAPED_UNICODE);
}


    public function getStudentsByDriverAndBranch(Request $request): JsonResponse
{
    $validated = $request->validate([
        'driver_id'  => 'required|exists:users,id',
        'school_id'  => 'required|exists:schools,id',
        'branch_id'  => 'nullable|exists:branches,id',
    ]);

    $students = Student::with([
        'school',
        'branch',
        'gradeClass',
        'academicBand.gate',
        'guardians',
        'supervisors',
        'bus',
    ])
    ->whereNotNull('bus_id') // الطلاب المرتبطين بباص
    ->where('school_id', $validated['school_id'])
    // ->when($validated['branch_id'], function ($query, $branchId) {
    //     $query->where('branch_id', $branchId);
    // })
    ->whereHas('bus', function ($query) use ($validated) {
        $query->where('driver_id', $validated['driver_id']);
    })
    ->orderBy('name_ar')
    ->get();

    return response()->json([
        'success' => true,
        'message' => 'تم جلب الطلاب المرتبطين بالسائق بنجاح',
        'data' => $students,
        'count' => $students->count(),
    ], 200, [], JSON_UNESCAPED_UNICODE);
}


    }
