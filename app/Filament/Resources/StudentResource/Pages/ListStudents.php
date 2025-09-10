<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Filament\Actions\ExportStudentsAction;
use App\Filament\Actions\ImportStudentsAction;
use App\Filament\Actions\DownloadStudentTemplateAction;
use App\Filament\Actions\RestoreStudentsAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DownloadStudentTemplateAction::make()
                ->button()
                ->size('sm'),
            ImportStudentsAction::make()
                ->button()
                ->size('sm'),
            ExportStudentsAction::make()
                ->button()
                ->size('sm'),
            // RestoreStudentsAction::make()
            //     ->button()
            //     ->size('sm'),
            Actions\CreateAction::make()
                ->label('إضافة طالب جديد')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTitle(): string
    {
        return 'إدارة الطلاب';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\StudentStatsWidget::class,
        ];
    }
}