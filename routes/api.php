<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Student;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// البحث عن الطلاب - API للبحث السريع
Route::get('/students/search', function (Request $request) {
    $search = $request->get('search', '');
    
    if (strlen($search) < 2) {
        return response()->json([]);
    }
    
    $students = Student::query()
        ->where('name_ar', 'like', "%{$search}%")
        ->orWhere('name_en', 'like', "%{$search}%")
        ->orWhere('code', 'like', "%{$search}%")
        ->orWhere('student_number', 'like', "%{$search}%")
        ->where('is_active', true)
        ->limit(50)
        ->get()
        ->map(function ($student) {
            return [
                'id' => $student->id,
                'name_ar' => $student->name_ar,
                'name_en' => $student->name_en,
                'code' => $student->code,
                'student_number' => $student->student_number,
                'gender' => $student->gender,
                'display_name' => "{$student->name_ar} - كود: {$student->code}",
            ];
        });
    
    return response()->json($students);
})->name('api.students.search');

// البحث عن أولياء الأمور
Route::get('/guardians/search', function (Request $request) {
    $search = $request->get('search', '');
    
    if (strlen($search) < 2) {
        return response()->json([]);
    }
    
    $guardians = \App\Models\Guardian::query()
        ->where('name_ar', 'like', "%{$search}%")
        ->orWhere('name_en', 'like', "%{$search}%")
        ->orWhere('phone', 'like', "%{$search}%")
        ->orWhere('national_id', 'like', "%{$search}%")
        ->where('is_active', true)
        ->with('students')
        ->limit(50)
        ->get()
        ->map(function ($guardian) {
            return [
                'id' => $guardian->id,
                'name_ar' => $guardian->name_ar,
                'name_en' => $guardian->name_en,
                'phone' => $guardian->phone,
                'relationship' => $guardian->relationship,
                'students_count' => $guardian->students->count(),
                'display_name' => "{$guardian->name_ar} - {$guardian->phone}",
            ];
        });
    
    return response()->json($guardians);
})->name('api.guardians.search');