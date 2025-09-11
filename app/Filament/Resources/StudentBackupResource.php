<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentBackupResource\Pages;
use App\Models\StudentBackup;
use App\Http\Controllers\Api\StudentRestoreController;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentBackupResource extends Resource
{
    protected static ?string $model = StudentBackup::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    
    protected static ?string $navigationLabel = 'النسخ الاحتياطية للطلاب';
    
    protected static ?string $modelLabel = 'نسخة احتياطية';
    
    protected static ?string $pluralModelLabel = 'النسخ الاحتياطية';

    protected static ?string $navigationGroup = 'إعدادات';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات النسخة الاحتياطية')
                    ->schema([
                        Forms\Components\TextInput::make('backup_name')
                            ->label('اسم النسخة الاحتياطية')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('students_count')
                                    ->label('عدد الطلاب')
                                    ->numeric()
                                    ->disabled(),

                                Forms\Components\TextInput::make('formatted_file_size')
                                    ->label('حجم الملف')
                                    ->disabled(),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('created_at')
                                    ->label('تاريخ الإنشاء')
                                    ->disabled(),

                                Forms\Components\DateTimePicker::make('last_restored_at')
                                    ->label('آخر استرداد')
                                    ->disabled(),
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('backup_name')
                    ->label('اسم النسخة الاحتياطية')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('عدد الطلاب')
                    ->numeric()
                    ->sortable()
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('formatted_file_size')
                    ->label('حجم الملف')
                    ->alignment('center'),

                Tables\Columns\IconColumn::make('file_exists')
                    ->label('حالة الملف')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('منشئ النسخة')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_restored_at')
                    ->label('آخر استرداد')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->placeholder('لم يتم الاسترداد'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->getStateUsing(function (StudentBackup $record) {
                        // if (!$record->file_exists()) {
                        //     return 'ملف مفقود';
                        // }
                        if ($record->last_restored_at) {
                            return 'مُستَردة';
                        }
                        return 'جديدة';
                    })
                    ->colors([
                        'success' => 'جديدة',
                        'warning' => 'مُستَردة',
                        'danger' => 'ملف مفقود',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('creator')
                    ->label('منشئ النسخة')
                    ->relationship('creator', 'name'),

                Tables\Filters\Filter::make('has_file')
                    ->label('الملف موجود')
                    ->query(fn (Builder $query) => $query->whereRaw('1=1')) // سيتم تطبيقها في afterStateUpdated
                    ->toggle(),

                Tables\Filters\Filter::make('restored')
                    ->label('تم الاسترداد منها')
                    ->query(fn (Builder $query) => $query->whereNotNull('last_restored_at'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                Action::make('restore')
                    ->label('استرداد')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('استرداد النسخة الاحتياطية')
                    ->modalDescription('هل أنت متأكد من استرداد هذه النسخة الاحتياطية؟ سيتم حذف جميع البيانات الحالية واستبدالها.')
                    ->action(function (StudentBackup $record) {
                        try {
                            $controller = new StudentRestoreController();
                            $request = new Request([
                                'restore_type' => 'restore_from_backup',
                                'backup_id' => $record->id
                            ]);
                            
                            $response = $controller->restoreFromBackup($request);
                            $responseData = $response->getData(true);
                            
                            if ($responseData['success']) {
                                Notification::make()
                                    ->title('تم الاسترداد بنجاح')
                                    ->body($responseData['message'])
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('فشل الاسترداد')
                                    ->body($responseData['message'])
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('خطأ في الاسترداد')
                                ->body('حدث خطأ: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                    // ->visible(fn (StudentBackup $record) => $record->file_exists()),

                Action::make('download')
                    ->label('تحميل')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (StudentBackup $record) {
                        // if (!$record->file_exists()) {
                        //     Notification::make()
                        //         ->title('ملف غير موجود')
                        //         ->body('ملف النسخة الاحتياطية غير موجود')
                        //         ->danger()
                        //         ->send();
                        //     return;
                        // }

                        $cleanName = Str::slug($record->backup_name, '_'); // أو استخدم preg_replace
                        $filename = 'backup_' . $cleanName . '_' . $record->created_at->format('Y-m-d') . '.xlsx';

                        return Storage::download($record->file_path, $filename);
                    }),
                    // ->visible(fn (StudentBackup $record) => $record->file_exists()),

                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // منع تعديل الحقول الحساسة
                        unset($data['file_path'], $data['file_size'], $data['students_count']);
                        return $data;
                    }),

                DeleteAction::make()
                    ->requiresConfirmation()
                    ->action(function (StudentBackup $record) {
                        // حذف الملف من التخزين
                        // if ($record->file_exists()) {
                        //     Storage::delete($record->file_path);
                        // }
                        $record->delete();
                        
                        Notification::make()
                            ->title('تم الحذف')
                            ->body('تم حذف النسخة الاحتياطية والملف المرتبط بها')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('delete_selected')
                        ->label('حذف المحدد')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                // if ($record->file_exists()) {
                                //     Storage::delete($record->file_path);
                                // }
                                $record->delete();
                            }
                            
                            Notification::make()
                                ->title('تم الحذف')
                                ->body('تم حذف ' . $records->count() . ' نسخة احتياطية')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentBackups::route('/'),
            'create' => Pages\CreateStudentBackup::route('/create'),
            'view' => Pages\ViewStudentBackup::route('/{record}'),
            'edit' => Pages\EditStudentBackup::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('school_id', auth()->user()?->school_id);
    }

    public static function canCreate(): bool
    {
        return false; // منع الإنشاء اليدوي - يتم الإنشاء عبر الـ Actions
    }
}