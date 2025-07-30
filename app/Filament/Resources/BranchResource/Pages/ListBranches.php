<?php

namespace App\Filament\Resources\BranchResource\Pages;

use App\Filament\Resources\BranchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\Branch;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Notifications\Notification;
class ListBranches extends ListRecords
{
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    protected function getTableActions(): array
    {
        return [
            EditAction::make(),

            DeleteAction::make()
                ->before(function (Branch $record) {
                    if (! $record->school?->allow_branch_deletion) {
                        Notification::make()
                            ->title('غير مسموح بحذف الفروع لهذه المدرسة')
                            ->danger()
                            ->send();

                        // منع الحذف
                        throw new \Exception('حذف الفرع غير مسموح لهذه المدرسة.');
                    }
                }),
        ];
    }
}
