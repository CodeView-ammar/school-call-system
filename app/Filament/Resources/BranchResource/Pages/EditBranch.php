<?php

namespace App\Filament\Resources\BranchResource\Pages;

use App\Filament\Resources\BranchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBranch extends EditRecord
{
    protected static string $resource = BranchResource::class;
    
    public $latitude;
    public $longitude;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    // protected function mutateFormDataBeforeFill(array $data): array
    // {
    //     $this->latitude = $data['latitude'] ?? 24.7136;
    //     $this->longitude = $data['longitude'] ?? 46.6753;
        
    //     return $data;
    // }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (auth()->user()?->school_id) {
            $data['school_id'] = auth()->user()->school_id;
        }
        return $data;
    }
}
