<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
class AuthController extends Controller
{
    use HasFactory, HasApiTokens;
    /**
     * تسجيل الدخول
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['البيانات المدخلة غير صحيحة.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['الحساب غير مفعل.'],
            ]);
        }

        // تحديث آخر تسجيل دخول
        $user->update(['last_login_at' => now()]);

        // إنشاء Token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح',
            'user' => [

                'id' => $user->id,
                "phone"=>$user->phone,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type,
                "name_ar"=>$user->name_ar,
                "name_en"=>$user->name_en,
                'school_id' => $user->school_id,
                'branch_ids'=>$user->branch_id,
                'permissions' => $user->getAllPermissions()->pluck('name'),
                "is_active"=>$user->is_active,
                "can_manage_school"=>$user->can_manage_school,
                "school_permissions"=>$user->school_permissions,
                'roles' => $user->getRoleNames(),
            ],
            'token' => $token,
        ]);
    }

    /**
     * تسجيل الخروج
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح'
        ]);
    }

    /**
     * الحصول على بيانات المستخدم الحالي
     */
    public function user(Request $request)
    {
        $user = $request->user()->load(['school', 'schools']);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'name_ar' => $user->name_ar,
                'name_en' => $user->name_en,
                'email' => $user->email,
                'phone' => $user->phone,
                'user_type' => $user->user_type,
                'school_id' => $user->school_id,
                'school' => $user->school,
                'accessible_schools' => $user->getAccessibleSchools(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'roles' => $user->getRoleNames(),
                'last_login_at' => $user->last_login_at,
            ]
        ]);
    }

    /**
     * تحديث كلمة المرور
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['كلمة المرور الحالية غير صحيحة.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'تم تحديث كلمة المرور بنجاح'
        ]);
    }

    /**
     * تحديث الملف الشخصي
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
        ]);

        $user->update($request->only(['name_ar', 'name_en', 'phone']));

        return response()->json([
            'message' => 'تم تحديث البيانات بنجاح',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'name_ar' => $user->name_ar,
                'name_en' => $user->name_en,
                'email' => $user->email,
                'phone' => $user->phone,
            ]
        ]);
    }
}