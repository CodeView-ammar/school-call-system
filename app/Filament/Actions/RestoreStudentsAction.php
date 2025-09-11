<?php

namespace App\Filament\Actions;

use App\Http\Controllers\Api\StudentRestoreController;
use App\Models\StudentBackup;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;

class RestoreStudentsAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'restore_students';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('استرداد البيانات')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->form([
                Section::make('خيارات الاسترداد')
                    ->description('اختر نوع عملية الاسترداد المطلوبة')
                    ->schema([
                        Select::make('restore_type')
                            ->label('نوع الاسترداد')
                            ->required()
                            ->options([
                                'reset_attendance' => '🔄 إعادة تعيين حالة الحضور',
                                'restore_from_backup' => '💾 الاسترداد من نسخة احتياطية',
                                'refresh_from_server' => '🌐 التحديث من الخادم',
                                'create_backup' => '💾 إنشاء نسخة احتياطية',
                            ])
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('backup_id', null)),

                        // خيارات إعادة تعيين الحضور
                        Select::make('reset_type')
                            ->label('نوع إعادة التعيين')
                            ->visible(fn (callable $get) => $get('restore_type') === 'reset_attendance')
                            ->options([
                                'all_absent' => 'جميع الطلاب غائبين',
                                'all_present' => 'جميع الطلاب حاضرين',
                                'clear_timestamps' => 'مسح أوقات الحضور فقط',
                            ])
                            ->default('all_absent'),

                        // اختيار النسخة الاحتياطية
                        Select::make('backup_id')
                            ->label('اختيار النسخة الاحتياطية')
                            ->visible(fn (callable $get) => $get('restore_type') === 'restore_from_backup')
                            ->required(fn (callable $get) => $get('restore_type') === 'restore_from_backup')
                            ->options(function () {
                                return StudentBackup::where('school_id', auth()->user()?->school_id)
                                    ->orderBy('created_at', 'desc')
                                    ->take(20)
                                    ->get()
                                    ->mapWithKeys(function ($backup) {
                                        $status = $backup->last_restored_at ? '(مُستَردة)' : '(جديدة)';
                                        return [
                                            $backup->id => sprintf(
                                                '%s %s - %d طالب - %s - %s',
                                                $backup->backup_name,
                                                $status,
                                                $backup->students_count,
                                                $backup->formatted_file_size,
                                                $backup->created_at->format('Y/m/d H:i')
                                            )
                                        ];
                                    });
                            })
                            ->placeholder('اختر النسخة الاحتياطية للاسترداد منها')
                            ->helperText('⚠️ سيتم حذف جميع البيانات الحالية واستبدالها ببيانات النسخة الاحتياطية')
                            ->searchable()
                            ->preload(),

                        // اسم النسخة الاحتياطية الجديدة
                        TextInput::make('backup_name')
                            ->label('اسم النسخة الاحتياطية')
                            ->visible(fn (callable $get) => $get('restore_type') === 'create_backup')
                            ->default(fn () => 'نسخة احتياطية - ' . now()->format('Y/m/d H:i'))
                            ->required(fn (callable $get) => $get('restore_type') === 'create_backup'),

                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->placeholder('اختياري - أضف أي ملاحظات حول هذه العملية')
                            ->rows(2),
                    ])
            ])
            ->action(function (array $data) {
                try {
                    $controller = new StudentRestoreController();
                    $request = new Request($data);
                    
                    $response = null;
                    
                    switch ($data['restore_type']) {
                        case 'reset_attendance':
                            $response = $controller->resetAttendance($request);
                            break;
                            
                        case 'restore_from_backup':
                            $response = $controller->restoreFromBackup($request);
                            break;
                            
                        case 'refresh_from_server':
                            $response = $controller->refreshFromServer($request);
                            break;
                            
                        case 'create_backup':
                            $response = $controller->createBackup($request);
                            break;
                    }
                    
                    if ($response) {
                        $responseData = $response->getData(true);
                        
                        if (isset($responseData['success']) && $responseData['success']) {
                            Notification::make()
                                ->title('تم بنجاح')
                                ->body($responseData['message'])
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('فشلت العملية')
                                ->body($responseData['message'] ?? 'حدث خطأ غير متوقع')
                                ->danger()
                                ->send();
                        }
                    }
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('خطأ في العملية')
                        ->body('حدث خطأ أثناء تنفيذ العملية: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->modalWidth('lg')
            ->modalSubmitActionLabel('تنفيذ العملية')
            ->modalCancelActionLabel('إلغاء')
            ->requiresConfirmation()
            ->modalDescription('تأكد من اختيار العملية المناسبة. بعض العمليات قد تؤثر على البيانات الحالية.');
    }
}