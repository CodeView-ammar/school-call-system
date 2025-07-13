<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    /**
     * الحصول على معرف المدرسة من المستخدم أو الطلب
     */
    protected function getSchoolId(Request $request)
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
     * التحقق من صلاحية الوصول للمدرسة
     */
    protected function canAccessSchool(Request $request, $schoolId)
    {
        $user = $request->user();
        
        // Super Admin يمكنه الوصول لجميع المدارس
        if ($user->user_type === 'super_admin') {
            return true;
        }
        
        // باقي المستخدمين مقيدون بمدرستهم
        return $user->school_id == $schoolId;
    }

    /**
     * إرجاع استجابة خطأ موحدة
     */
    protected function errorResponse($message, $errors = null, $statusCode = 422)
    {
        $response = ['message' => $message];
        
        if ($errors) {
            $response['errors'] = $errors;
        }
        
        return response()->json($response, $statusCode);
    }

    /**
     * إرجاع استجابة نجح موحدة
     */
    protected function successResponse($message, $data = null, $statusCode = 200)
    {
        $response = ['message' => $message];
        
        if ($data) {
            $response['data'] = $data;
        }
        
        return response()->json($response, $statusCode);
    }
}