<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bus;
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
}