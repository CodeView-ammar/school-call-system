<?php

namespace App\Filament\Resources\StudentCallResource\Pages;

use App\Filament\Resources\StudentCallResource;
use Filament\Actions;
use App\Models\StudentCall;
use Filament\Resources\Pages\ListRecords;
use Carbon\Carbon;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class ListStudentCalls extends ListRecords
{
    protected static string $resource = StudentCallResource::class;

    public string $selectedDate;

    public function mount(): void
    {
        parent::mount();
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function changeDate(int $days): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->addDays($days)->format('Y-m-d');
        $this->resetTable(); // يعيد تحميل الجدول بالبيانات المحدثة
    }

    protected function getTableQuery(): Builder
    {
        return StudentCall::query()
            ->whereDate('call_cdate', $this->selectedDate)
            ->when(auth()->user()?->school_id, fn ($query) =>
                $query->where('school_id', auth()->user()->school_id)
            );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('previousDate')
                ->label('اليوم السابق')
                ->icon('heroicon-m-chevron-right')
                ->action(fn () => $this->changeDate(-1)),

            Action::make('nextDate')
                ->label('اليوم التالي')
                ->icon('heroicon-m-chevron-left')
                ->action(fn () => $this->changeDate(1)),
        ];
    }

    public function getTitle(): string
    {
        return 'ندائات اليوم: ' . Carbon::parse($this->selectedDate)->translatedFormat('Y-m-d');
    }
}