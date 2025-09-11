<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Filament\Actions\ExportStudentsAction;
use App\Filament\Actions\ImportStudentsAction;
use App\Filament\Actions\DownloadStudentTemplateAction;
use App\Filament\Actions\RestoreStudentsAction;
use App\Http\Controllers\Api\StudentRestoreController;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;

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
           
            Actions\Action::make('quick_backup')
                ->label('نسخة احتياطية سريعة')
                ->icon('heroicon-o-archive-box')
                ->color('info')
                ->button()
                ->size('sm')
                ->action(function () {
                    try {
                        $controller = new StudentRestoreController();
                        $request = new Request([
                            'restore_type' => 'create_backup',
                            'backup_name' => 'نسخة احتياطية سريعة - ' . now()->format('Y/m/d H:i'),
                            'notes' => 'تم الإنشاء من صفحة إدارة الطلاب'
                        ]);

                        $response = $controller->createBackup($request);
                        $responseData = $response->getData(true);

                        if ($responseData['success']) {
                            Notification::make()
                                ->title('تم إنشاء النسخة الاحتياطية')
                                ->body($responseData['message'])
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('فشل إنشاء النسخة الاحتياطية')
                                ->body($responseData['message'])
                                ->danger()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('خطأ في إنشاء النسخة الاحتياطية')
                            ->body('حدث خطأ: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
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