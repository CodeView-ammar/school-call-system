<?php

namespace App\Filament\Resources\StudentBackupResource\Pages;

use App\Filament\Resources\StudentBackupResource;
use App\Http\Controllers\Api\StudentRestoreController;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;

class ListStudentBackups extends ListRecords
{
    protected static string $resource = StudentBackupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_backup')
                ->label('إنشاء نسخة احتياطية جديدة')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->form([
                    TextInput::make('backup_name')
                        ->label('اسم النسخة الاحتياطية')
                        ->required()
                        ->default('نسخة احتياطية - ' . now()->format('Y/m/d H:i'))
                        ->maxLength(255),

                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->placeholder('اختياري - أضف أي ملاحظات حول هذه النسخة الاحتياطية')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    try {
                        $controller = new StudentRestoreController();
                        $request = new Request([
                            'restore_type' => 'create_backup',
                            'backup_name' => $data['backup_name'],
                            'notes' => $data['notes'] ?? null
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
                })
                ->modalWidth('md')
                ->modalSubmitActionLabel('إنشاء النسخة الاحتياطية')
                ->modalCancelActionLabel('إلغاء'),

            Actions\Action::make('cleanup_backups')
                ->label('تنظيف النسخ القديمة')
                ->icon('heroicon-o-trash')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('تنظيف النسخ الاحتياطية القديمة')
                ->modalDescription('سيتم حذف النسخ الاحتياطية الأقدم من 30 يوماً والتي لم يتم الاسترداد منها.')
                ->action(function () {
                    $oldBackups = \App\Models\StudentBackup::where('school_id', auth()->user()?->school_id)
                        ->where('created_at', '<', now()->subDays(30))
                        ->whereNull('last_restored_at')
                        ->get();

                    $deletedCount = 0;
                    foreach ($oldBackups as $backup) {
                        if ($backup->file_exists()) {
                            \Storage::delete($backup->file_path);
                        }
                        $backup->delete();
                        $deletedCount++;
                    }

                    Notification::make()
                        ->title('تم التنظيف')
                        ->body("تم حذف {$deletedCount} نسخة احتياطية قديمة")
                        ->success()
                        ->send();
                })
        ];
    }
}