<?php

namespace App\Filament\Actions;

use App\Http\Controllers\Api\StudentExportController;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use App\Models\Branch;

class ExportStudentsAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'export_students';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('تصدير الطلاب')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->form([
                Section::make('خيارات التصدير')
                    ->description('اختر الفلاتر المناسبة لتصدير البيانات المطلوبة')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('branch_id')
                                ->label('الفرع')
                                ->placeholder('جميع الفروع')
                                ->options(function () {
                                    $query = Branch::query();
                                    if (auth()->user()?->school_id) {
                                        $query->where('school_id', auth()->user()->school_id);
                                    }
                                    return $query->pluck('name_ar', 'id');
                                })
                                ->searchable(),

                            Select::make('gender')
                                ->label('الجنس')
                                ->placeholder('الجميع')
                                ->options([
                                    'male' => 'ذكر',
                                    'female' => 'أنثى',
                                ]),
                        ]),

                        Grid::make(2)->schema([
                            Select::make('is_present')
                                ->label('حالة الحضور')
                                ->placeholder('الجميع')
                                ->options([
                                    '1' => 'حاضر',
                                    '0' => 'غائب',
                                ]),

                            Select::make('is_active')
                                ->label('حالة النشاط')
                                ->placeholder('الجميع')
                                ->options([
                                    '1' => 'نشط',
                                    '0' => 'غير نشط',
                                ]),
                        ]),

                        Grid::make(2)->schema([
                            DatePicker::make('date_from')
                                ->label('من تاريخ')
                                ->placeholder('اختياري'),

                            DatePicker::make('date_to')
                                ->label('إلى تاريخ')
                                ->placeholder('اختياري'),
                        ]),
                    ])
            ])
            ->action(function (array $data) {
                try {
                    $controller = new StudentExportController();
                    
                    // تحويل البيانات إلى Request object
                    $request = new Request($data);
                    
                    // استدعاء دالة التصدير
                    $response = $controller->exportToExcel($request);
                    
                    if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
                        Notification::make()
                            ->title('تم التصدير بنجاح')
                            ->body('سيتم تحميل الملف تلقائياً')
                            ->success()
                            ->send();
                        
                        return $response;
                    } else {
                        $responseData = $response->getData(true);
                        
                        if (isset($responseData['success']) && !$responseData['success']) {
                            Notification::make()
                                ->title('فشل في التصدير')
                                ->body($responseData['message'] ?? 'حدث خطأ غير متوقع')
                                ->danger()
                                ->send();
                        }
                    }
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('خطأ في التصدير')
                        ->body('حدث خطأ أثناء تصدير البيانات: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->modalWidth('lg')
            ->modalSubmitActionLabel('تصدير إلى Excel')
            ->modalCancelActionLabel('إلغاء');
    }
}