<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // التأكد من وجود school_id
        if (!isset($data['school_id']) || empty($data['school_id'])) {
            $data['school_id'] = auth()->user()->school_id;
        }

        // إنشاء رقم طالب تلقائي إذا لم يكن موجوداً
        if (!isset($data['student_number']) || empty($data['student_number'])) {
            $data['student_number'] = $this->generateStudentNumber();
        }

        return $data;
    }

    private function generateStudentNumber(): string
    {
        $schoolId = auth()->user()->school_id;
        $year = now()->year;
        
        // البحث عن آخر رقم طالب في المدرسة
        $lastStudent = \App\Models\Student::where('school_id', $schoolId)
            ->where('student_number', 'like', $year . '%')
            ->orderBy('student_number', 'desc')
            ->first();

        if ($lastStudent) {
            $lastNumber = (int) substr($lastStudent->student_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $year . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
