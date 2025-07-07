<?php

namespace App\Filament\Forms\Components;

use App\Models\Student;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Concerns\HasState;

class StudentSelector extends Field
{
    use HasState;

    protected string $view = 'filament.forms.components.student-selector';

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);
    }

    public function getSearchResults(string $search): array
    {
        if (strlen($search) < 2) {
            return [];
        }

        return Student::query()
            ->where('name_ar', 'like', "%{$search}%")
            ->orWhere('name_en', 'like', "%{$search}%")
            ->orWhere('code', 'like', "%{$search}%")
            ->orWhere('student_number', 'like', "%{$search}%")
            ->active()
            ->limit(50)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name_ar' => $student->name_ar,
                    'name_en' => $student->name_en,
                    'code' => $student->code,
                    'student_number' => $student->student_number,
                    'display_name' => "{$student->name_ar} - كود: {$student->code}",
                    'gender' => $student->gender,
                    'is_active' => $student->is_active,
                ];
            })
            ->toArray();
    }

    public function getSelectedStudentsData(): array
    {
        $selectedIds = $this->getState() ?? [];
        
        if (empty($selectedIds)) {
            return [];
        }

        return Student::whereIn('id', $selectedIds)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name_ar' => $student->name_ar,
                    'code' => $student->code,
                    'student_number' => $student->student_number,
                    'gender' => $student->gender,
                    'display_name' => "{$student->name_ar} - كود: {$student->code}",
                ];
            })
            ->toArray();
    }
}