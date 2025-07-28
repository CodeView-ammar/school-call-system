<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Maatwebsite\Excel\Facades\Excel;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

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
        return Attendance::query()
            ->whereDate('attendance_date', $this->selectedDate)
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

        Action::make('downloadAllExcel')
            ->label('تحميل كل الحضور')
            ->icon('heroicon-o-arrow-down-tray')
            ->action(function () {
                return Excel::download(new \App\Exports\AttendancesExport(), 'attendances_all.xlsx');
            }),

        Action::make('downloadRangeExcel')
            ->label('تحميل من تاريخ إلى تاريخ')
            ->form([
                DatePicker::make('from_date')
                    ->label('من تاريخ')
                    ->required(),

                DatePicker::make('to_date')
                    ->label('إلى تاريخ')
                    ->required(),
            ])
            ->action(function (array $data) {
                return Excel::download(
                    new \App\Exports\AttendancesExport($data['from_date'], $data['to_date']),
                    'attendances_range.xlsx'
                );
            }),
    ];
}
    public function getTitle(): string
    {
        return 'الحضور والغياب: ' . Carbon::parse($this->selectedDate)->translatedFormat('Y-m-d');
    }
}
