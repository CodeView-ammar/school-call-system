<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
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
     * التحقق من صلاحية إدارة المستخدمين
     */
    private function canManageUsers(Request $request)
    {
        $user = $request->user();
        return in_array($user->user_type, ['super_admin', 'school_admin']);
    }

    /**
     * عرض قائمة المستخدمين
     */
    public function index(Request $request)
    {
        if (!$this->canManageUsers($request)) {
            return response()->json(['message' => 'غير مصرح لك بالوصول لهذه البيانات'], 403);
        }

        $schoolId = $this->getSchoolId($request);
        
        $query = User::with(['school', 'roles', 'permissions']);

        // Super Admin يرى جميع المستخدمين أو مستخدمي مدرسة معينة
        if ($request->user()->user_type === 'super_admin') {
            if ($schoolId) {
                $query->where('school_id', $schoolId);
            }
        } else {
            // مدير المدرسة يرى مستخدمي مدرسته فقط
            $query->where('school_id', $schoolId);
        }

        // فلترة حسب النشاط
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // فلترة حسب نوع المستخدم
        if ($request->has('user_type') && $request->user_type) {
            $query->where('user_type', $request->user_type);
        }

        // البحث النصي
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // ترتيب النتائج
        $sortBy = $request->get('sort_by', 'name_ar');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // الحصول على النتائج
        $perPage = min($request->get('per_page', 15), 100);
        $users = $query->paginate($perPage);

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ]
        ]);
    }

    /**
     * إنشاء مستخدم جديد
     */
    public function store(Request $request)
    {
        if (!$this->canManageUsers($request)) {
            return response()->json(['message' => 'غير مصرح لك بالوصول لهذه البيانات'], 403);
        }

        // تعيين معرف المدرسة تلقائياً (إلا للـ super admin)
        if ($request->user()->user_type !== 'super_admin') {
            $schoolId = $this->getSchoolId($request);
            $request->merge(['school_id' => $schoolId]);
        }

        $validator = Validator::make($request->all(), [
            'school_id' => 'required|exists:schools,id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'user_type' => ['required', Rule::in(['super_admin', 'school_admin', 'teacher', 'staff', 'driver', 'supervisor'])],
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $validator->errors()
            ], 422);
        }

        $userData = $request->all();
        $userData['password'] = Hash::make($request->password);

        $user = User::create($userData);

        return response()->json([
            'message' => 'تم إنشاء المستخدم بنجاح',
            'data' => $user->load(['school', 'roles', 'permissions'])
        ], 201);
    }

    /**
     * عرض مستخدم محدد
     */
    public function show(Request $request, $id)
    {
        if (!$this->canManageUsers($request)) {
            return response()->json(['message' => 'غير مصرح لك بالوصول لهذه البيانات'], 403);
        }

        $query = User::with(['school', 'roles', 'permissions']);

        // Super Admin يرى جميع المستخدمين
        if ($request->user()->user_type === 'super_admin') {
            $user = $query->findOrFail($id);
        } else {
            // مدير المدرسة يرى مستخدمي مدرسته فقط
            $schoolId = $this->getSchoolId($request);
            $user = $query->where('school_id', $schoolId)->findOrFail($id);
        }

        return response()->json([
            'data' => $user
        ]);
    }

    /**
     * تحديث مستخدم
     */
    public function update(Request $request, $id)
    {
        if (!$this->canManageUsers($request)) {
            return response()->json(['message' => 'غير مصرح لك بالوصول لهذه البيانات'], 403);
        }

        $query = User::query();

        // Super Admin يمكنه تحديث جميع المستخدمين
        if ($request->user()->user_type === 'super_admin') {
            $user = $query->findOrFail($id);
        } else {
            // مدير المدرسة يحدث مستخدمي مدرسته فقط
            $schoolId = $this->getSchoolId($request);
            $user = $query->where('school_id', $schoolId)->findOrFail($id);
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => 'sometimes|required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'phone' => 'sometimes|required|string|max:20',
            'user_type' => ['sometimes', 'required', Rule::in(['super_admin', 'school_admin', 'teacher', 'staff', 'driver', 'supervisor'])],
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->all());

        return response()->json([
            'message' => 'تم تحديث المستخدم بنجاح',
            'data' => $user->load(['school', 'roles', 'permissions'])
        ]);
    }

    /**
     * حذف مستخدم
     */
    public function destroy(Request $request, $id)
    {
        if (!$this->canManageUsers($request)) {
            return response()->json(['message' => 'غير مصرح لك بالوصول لهذه البيانات'], 403);
        }

        $query = User::query();

        // Super Admin يمكنه حذف جميع المستخدمين
        if ($request->user()->user_type === 'super_admin') {
            $user = $query->findOrFail($id);
        } else {
            // مدير المدرسة يحذف مستخدمي مدرسته فقط
            $schoolId = $this->getSchoolId($request);
            $user = $query->where('school_id', $schoolId)->findOrFail($id);
        }

        // منع حذف المستخدم الحالي
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'لا يمكن حذف حسابك الشخصي'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'message' => 'تم حذف المستخدم بنجاح'
        ]);
    }

    /**
     * تغيير كلمة المرور
     */
    public function changePassword(Request $request, $id)
    {
        if (!$this->canManageUsers($request)) {
            return response()->json(['message' => 'غير مصرح لك بالوصول لهذه البيانات'], 403);
        }

        $query = User::query();

        // Super Admin يمكنه تغيير كلمة مرور جميع المستخدمين
        if ($request->user()->user_type === 'super_admin') {
            $user = $query->findOrFail($id);
        } else {
            // مدير المدرسة يغير كلمة مرور مستخدمي مدرسته فقط
            $schoolId = $this->getSchoolId($request);
            $user = $query->where('school_id', $schoolId)->findOrFail($id);
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'تم تغيير كلمة المرور بنجاح'
        ]);
    }

    /**
     * تفعيل/إلغاء تفعيل المستخدم
     */
    public function toggleStatus(Request $request, $id)
    {
        if (!$this->canManageUsers($request)) {
            return response()->json(['message' => 'غير مصرح لك بالوصول لهذه البيانات'], 403);
        }

        $query = User::query();

        // Super Admin يمكنه تفعيل/إلغاء تفعيل جميع المستخدمين
        if ($request->user()->user_type === 'super_admin') {
            $user = $query->findOrFail($id);
        } else {
            // مدير المدرسة يفعل/يلغي تفعيل مستخدمي مدرسته فقط
            $schoolId = $this->getSchoolId($request);
            $user = $query->where('school_id', $schoolId)->findOrFail($id);
        }

        // منع إلغاء تفعيل المستخدم الحالي
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'لا يمكن إلغاء تفعيل حسابك الشخصي'
            ], 400);
        }

        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'message' => $user->is_active ? 'تم تفعيل المستخدم' : 'تم إلغاء تفعيل المستخدم',
            'data' => $user
        ]);
    }

    /**
     * إحصائيات المستخدمين
     */
    public function statistics(Request $request)
    {
        if (!$this->canManageUsers($request)) {
            return response()->json(['message' => 'غير مصرح لك بالوصول لهذه البيانات'], 403);
        }

        $query = User::query();

        // Super Admin يرى إحصائيات جميع المستخدمين أو مدرسة معينة
        if ($request->user()->user_type === 'super_admin') {
            $schoolId = $this->getSchoolId($request);
            if ($schoolId) {
                $query->where('school_id', $schoolId);
            }
        } else {
            // مدير المدرسة يرى إحصائيات مستخدمي مدرسته فقط
            $schoolId = $this->getSchoolId($request);
            $query->where('school_id', $schoolId);
        }

        $stats = [
            'total_users' => $query->count(),
            'active_users' => $query->where('is_active', true)->count(),
            'inactive_users' => $query->where('is_active', false)->count(),
            'by_type' => [
                'super_admin' => $query->where('user_type', 'super_admin')->count(),
                'school_admin' => $query->where('user_type', 'school_admin')->count(),
                'teacher' => $query->where('user_type', 'teacher')->count(),
                'staff' => $query->where('user_type', 'staff')->count(),
                'driver' => $query->where('user_type', 'driver')->count(),
                'supervisor' => $query->where('user_type', 'supervisor')->count(),
            ]
        ];

        return response()->json($stats);
    }
}