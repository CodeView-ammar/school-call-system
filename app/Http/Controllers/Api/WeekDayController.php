<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\WeekDay;
class WeekDayController extends Controller
{
    // جلب كل الأيام
public function index(Request $request)
{
    $query = WeekDay::query();

    // فلترة حسب school_id
    if ($request->has('school_id')) {
        $query->where('school_id', $request->school_id);
    }

    // الحصول على اسم اليوم الحالي باللغة العربية
    $todayName = Carbon::now()->locale('ar')->dayName;
    // dd($todayName);
    // فلترة حسب اسم اليوم الحالي
    $query->where('day', $todayName);

    // فلترة حسب الأيام غير النشطة فقط
    $query->where('day_inactive', true);

    return response()->json([
        'status' => true,
        'data' => $query->get()
    ]);
}
    // جلب يوم معين
    public function show($id)
    {
        $day = WeekDay::find($id);
        if (!$day) {
            return response()->json(['status' => false, 'message' => 'Not Found'], 404);
        }

        return response()->json(['status' => true, 'data' => $day]);
    }

    // إضافة يوم
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'branch_id' => 'required|exists:branches,id',
            'day' => 'required|string',
            'time_from' => 'required|date_format:H:i:s',
            'time_to' => 'required|date_format:H:i:s',
            'day_inactive' => 'boolean',
        ]);

        $day = WeekDay::create($validated);

        return response()->json(['status' => true, 'data' => $day]);
    }

    // تحديث يوم
    public function update(Request $request, $id)
    {
        $day = WeekDay::find($id);
        if (!$day) {
            return response()->json(['status' => false, 'message' => 'Not Found'], 404);
        }

        $validated = $request->validate([
            'day' => 'sometimes|string',
            'time_from' => 'sometimes|date_format:H:i:s',
            'time_to' => 'sometimes|date_format:H:i:s',
            'day_inactive' => 'sometimes|boolean',
        ]);

        $day->update($validated);

        return response()->json(['status' => true, 'data' => $day]);
    }

    // حذف يوم
    public function destroy($id)
    {
        $day = WeekDay::find($id);
        if (!$day) {
            return response()->json(['status' => false, 'message' => 'Not Found'], 404);
        }

        $day->delete();

        return response()->json(['status' => true, 'message' => 'Deleted']);
    }
}
