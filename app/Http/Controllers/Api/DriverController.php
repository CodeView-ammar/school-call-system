<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DriverController extends Controller
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
     * عرض قائمة السائقين
     */
    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId($request);
        
        $query = Driver::with(['buses', 'school'])
            ->where('school_id', $schoolId);

        // فلترة حسب النشاط
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // البحث النصي
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('license_number', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%");
            });
        }

        // ترتيب النتائج
        $sortBy = $request->get('sort_by', 'name_ar');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // الحصول على النتائج
        $perPage = min($request->get('per_page', 15), 100);
        $drivers = $query->paginate($perPage);

        return response()->json([
            'data' => $drivers->items(),
            'meta' => [
                'current_page' => $drivers->currentPage(),
                'last_page' => $drivers->lastPage(),
                'per_page' => $drivers->perPage(),
                'total' => $drivers->total(),
            ]
        ]);
    }

    /**
     * إنشاء سائق جديد
     */
    public function store(Request $request)
    {
        // تعيين معرف المدرسة تلقائياً
        $schoolId = $this->getSchoolId($request);
        $request->merge(['school_id' => $schoolId]);

        $validator = Validator::make($request->all(), [
            'school_id' => 'required|exists:schools,id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'national_id' => 'required|string|max:20|unique:drivers,national_id',
            'license_number' => 'required|string|max:50|unique:drivers,license_number',
            'license_type' => 'required|string|max:50',
            'license_expiry_date' => 'required|date',
            'date_of_birth' => 'required|date',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:20',
            'hire_date' => 'required|date',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $validator->errors()
            ], 422);
        }

        $driver = Driver::create($request->all());

        return response()->json([
            'message' => 'تم إنشاء السائق بنجاح',
            'data' => $driver->load(['school', 'buses'])
        ], 201);
    }

    /**
     * عرض سائق محدد
     */
    public function show(Request $request, $id)
    {
        $schoolId = $this->getSchoolId($request);
        
        $driver = Driver::with(['school', 'buses'])
            ->where('school_id', $schoolId)
            ->findOrFail($id);

        return response()->json([
            'data' => $driver
        ]);
    }

    /**
     * تحديث سائق
     */
    public function update(Request $request, $id)
    {
        $schoolId = $this->getSchoolId($request);
        
        $driver = Driver::where('school_id', $schoolId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name_ar' => 'sometimes|required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'national_id' => 'sometimes|required|string|max:20|unique:drivers,national_id,' . $id,
            'license_number' => 'sometimes|required|string|max:50|unique:drivers,license_number,' . $id,
            'license_type' => 'sometimes|required|string|max:50',
            'license_expiry_date' => 'sometimes|required|date',
            'date_of_birth' => 'sometimes|required|date',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:20',
            'hire_date' => 'sometimes|required|date',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $validator->errors()
            ], 422);
        }

        $driver->update($request->all());

        return response()->json([
            'message' => 'تم تحديث السائق بنجاح',
            'data' => $driver->load(['school', 'buses'])
        ]);
    }

    /**
     * حذف سائق
     */
    public function destroy(Request $request, $id)
    {
        $schoolId = $this->getSchoolId($request);
        
        $driver = Driver::where('school_id', $schoolId)->findOrFail($id);

        // التحقق من وجود حافلات مرتبطة
        if ($driver->buses()->count() > 0) {
            return response()->json([
                'message' => 'لا يمكن حذف السائق لأنه مرتبط بحافلات'
            ], 400);
        }

        $driver->delete();

        return response()->json([
            'message' => 'تم حذف السائق بنجاح'
        ]);
    }

    /**
     * تفعيل/إلغاء تفعيل السائق
     */
    public function toggleStatus(Request $request, $id)
    {
        $schoolId = $this->getSchoolId($request);
        
        $driver = Driver::where('school_id', $schoolId)->findOrFail($id);
        $driver->update(['is_active' => !$driver->is_active]);

        return response()->json([
            'message' => $driver->is_active ? 'تم تفعيل السائق' : 'تم إلغاء تفعيل السائق',
            'data' => $driver
        ]);
    }

    /**
     * إحصائيات السائقين
     */
    public function statistics(Request $request)
    {
        $schoolId = $this->getSchoolId($request);
        
        $drivers = Driver::where('school_id', $schoolId);

        $stats = [
            'total_drivers' => $drivers->count(),
            'active_drivers' => $drivers->where('is_active', true)->count(),
            'inactive_drivers' => $drivers->where('is_active', false)->count(),
            'drivers_with_buses' => $drivers->whereHas('buses')->count(),
            'drivers_without_buses' => $drivers->whereDoesntHave('buses')->count(),
            'expired_licenses' => $drivers->where('license_expiry_date', '<', now())->count(),
            'expiring_soon' => $drivers->where('license_expiry_date', '<=', now()->addMonth())->count(),
        ];

        return response()->json($stats);
    }

    /**
     * البحث السريع عن السائقين
     */
    public function search(Request $request)
    {
        $search = $request->get('search', '');
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $schoolId = $this->getSchoolId($request);
        
        $drivers = Driver::where('school_id', $schoolId)
            ->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('license_number', 'like', "%{$search}%");
            })
            ->where('is_active', true)
            ->limit(50)
            ->get()
            ->map(function ($driver) {
                return [
                    'id' => $driver->id,
                    'name_ar' => $driver->name_ar,
                    'name_en' => $driver->name_en,
                    'phone' => $driver->phone,
                    'license_number' => $driver->license_number,
                    'display_name' => "{$driver->name_ar} - {$driver->license_number}",
                ];
            });
        
        return response()->json($drivers);
    }
}