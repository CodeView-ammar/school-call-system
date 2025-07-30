<?php
namespace App\Filament\Resources\MorningStudentCallResource\Pages;

use App\Filament\Resources\MorningStudentCallResource;
use App\Models\StudentCall;
use Filament\Pages\Actions;
use Filament\Resources\Pages\Page;

class ShowMorningStudentCalls extends Page
{
    protected static string $resource = MorningStudentCallResource::class;

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