<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    // جميع العطلات
    public function index(Request $request)
    {
        $query = Holiday::query();
        validator($request->all(), [
            'school_id' => 'required|exists:schools,id',
        ])->validate();
        
        
        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }
        $query->where(function ($q) {
            $q->where('is_active', true);
        });
        $holidays = $query->latest()->get();

        return response()->json([
            'status' => true,
            'data' => $holidays,
        ]);
    }

    // العطلات النشطة فقط
    public function active()
    {
        $holidays = Holiday::active()->get();

        return response()->json([
            'status' => true,
            'data' => $holidays,
        ]);
    }

    // العطلات الحالية فقط
    public function current()
    {
        $holidays = Holiday::current()->get();

        return response()->json([
            'status' => true,
            'data' => $holidays,
        ]);
    }

    // عرض عطلة واحدة
    public function show($id)
    {
        $holiday = Holiday::find($id);

        if (!$holiday) {
            return response()->json([
                'status' => false,
                'message' => 'Holiday not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $holiday,
        ]);
    }
}
