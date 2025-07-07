<?php

namespace App\Livewire;

use App\Models\Student;
use Livewire\Component;

class StudentSearchComponent extends Component
{
    public $search = '';
    public $selectedStudents = [];
    public $searchResults = [];
    public $showResults = false;

    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            $this->searchResults = Student::query()
                ->where('name_ar', 'like', '%' . $this->search . '%')
                ->orWhere('name_en', 'like', '%' . $this->search . '%')
                ->orWhere('code', 'like', '%' . $this->search . '%')
                ->orWhere('student_number', 'like', '%' . $this->search . '%')
                ->active()
                ->limit(10)
                ->get()
                ->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'name_ar' => $student->name_ar,
                        'code' => $student->code,
                        'student_number' => $student->student_number,
                        'display_name' => "{$student->name_ar} - كود: {$student->code}"
                    ];
                })
                ->toArray();
            
            $this->showResults = true;
        } else {
            $this->searchResults = [];
            $this->showResults = false;
        }
    }

    public function selectStudent($studentId)
    {
        $student = collect($this->searchResults)->firstWhere('id', $studentId);
        
        if ($student && !in_array($studentId, array_column($this->selectedStudents, 'id'))) {
            $this->selectedStudents[] = $student;
            $this->dispatch('student-selected', $student);
        }
        
        $this->reset(['search', 'searchResults', 'showResults']);
    }

    public function removeStudent($index)
    {
        if (isset($this->selectedStudents[$index])) {
            $student = $this->selectedStudents[$index];
            unset($this->selectedStudents[$index]);
            $this->selectedStudents = array_values($this->selectedStudents);
            $this->dispatch('student-removed', $student);
        }
    }

    public function render()
    {
        return view('livewire.student-search-component');
    }
}