<?php

namespace App\Http\Livewire;

use App\Models\Guardian;
use App\Models\Student;
use Livewire\Component;
use Illuminate\Support\Collection;

class GuardianStudentManager extends Component
{
    public Guardian $guardian;
    public $selectedStudents = [];
    public $searchTerm = '';
    public $searchResults = [];
    public $showSearchResults = false;
    
    public function mount(Guardian $guardian)
    {
        $this->guardian = $guardian;
        $this->loadSelectedStudents();
    }
    
    public function loadSelectedStudents()
    {
        $this->selectedStudents = $this->guardian->students()
            ->with('guardians')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name_ar' => $student->name_ar,
                    'code' => $student->code,
                    'student_number' => $student->student_number,
                    'is_primary' => $student->pivot->is_primary ?? false,
                ];
            })
            ->toArray();
    }
    
    public function updatedSearchTerm()
    {
        if (strlen($this->searchTerm) >= 2) {
            $currentStudentIds = collect($this->selectedStudents)->pluck('id')->toArray();
            
            $this->searchResults = Student::query()
                ->where(function ($query) {
                    $query->where('name_ar', 'like', '%' . $this->searchTerm . '%')
                          ->orWhere('name_en', 'like', '%' . $this->searchTerm . '%')
                          ->orWhere('code', 'like', '%' . $this->searchTerm . '%')
                          ->orWhere('student_number', 'like', '%' . $this->searchTerm . '%');
                })
                ->whereNotIn('id', $currentStudentIds)
                ->active()
                ->limit(10)
                ->get()
                ->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'name_ar' => $student->name_ar,
                        'code' => $student->code,
                        'student_number' => $student->student_number,
                    ];
                })
                ->toArray();
                
            $this->showSearchResults = true;
        } else {
            $this->searchResults = [];
            $this->showSearchResults = false;
        }
    }
    
    public function addStudent($studentId, $isPrimary = true)
    {
        $student = Student::find($studentId);
        
        if (!$student) {
            session()->flash('error', 'الطالب غير موجود');
            return;
        }
        
        // التحقق من عدم وجود الطالب مسبقاً
        $existingStudent = collect($this->selectedStudents)->firstWhere('id', $studentId);
        if ($existingStudent) {
            session()->flash('warning', 'الطالب مرتبط مسبقاً');
            return;
        }
        
        // إضافة الطالب للقائمة
        $this->selectedStudents[] = [
            'id' => $student->id,
            'name_ar' => $student->name_ar,
            'code' => $student->code,
            'student_number' => $student->student_number,
            'is_primary' => $isPrimary,
            'is_new' => true, // للتمييز بين الجديد والموجود
        ];
        
        // مسح البحث
        $this->reset(['searchTerm', 'searchResults', 'showSearchResults']);
        
        session()->flash('success', 'تم إضافة الطالب بنجاح');
    }
    
    public function removeStudent($index)
    {
        if (isset($this->selectedStudents[$index])) {
            unset($this->selectedStudents[$index]);
            $this->selectedStudents = array_values($this->selectedStudents);
            session()->flash('success', 'تم إزالة الطالب');
        }
    }
    
    public function togglePrimaryStatus($index)
    {
        if (isset($this->selectedStudents[$index])) {
            $this->selectedStudents[$index]['is_primary'] = !$this->selectedStudents[$index]['is_primary'];
            session()->flash('success', 'تم تحديث حالة ولي الأمر الرئيسي');
        }
    }
    
    public function saveChanges()
    {
        try {
            // تجهيز البيانات للمزامنة
            $syncData = [];
            foreach ($this->selectedStudents as $student) {
                $syncData[$student['id']] = [
                    'is_primary' => $student['is_primary']
                ];
            }
            
            // مزامنة الطلاب مع ولي الأمر
            $this->guardian->students()->sync($syncData);
            
            session()->flash('success', 'تم حفظ التغييرات بنجاح');
            
            // إعادة تحميل البيانات
            $this->loadSelectedStudents();
            
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ أثناء حفظ التغييرات: ' . $e->getMessage());
        }
    }
    
    public function render()
    {
        return view('livewire.guardian-student-manager');
    }
}