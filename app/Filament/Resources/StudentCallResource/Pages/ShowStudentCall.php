<?php
namespace App\Filament\Resources\StudentCallResource\Pages;

use App\Filament\Resources\StudentCallResource;
use App\Models\StudentCall;
use Filament\Pages\Actions;
use Filament\Resources\Pages\Page;

class ShowStudentCall extends Page
{
    protected static string $resource = StudentCallResource::class;

    public StudentCall $studentCall;

    protected static string $view = 'filament.resources.student-call-resource.pages.show-student-call';

    public function mount(StudentCall $studentCall): void
    {
        $this->studentCall = $studentCall;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view(static::$view, [
            'studentCall' => $this->studentCall,
            'logs' => $this->studentCall->studentCallLogs, // استخدم العلاقة الصحيحة هنا
        ]);
    }
}