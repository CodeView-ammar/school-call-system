<?php

namespace App\Filament\Resources\BranchResource\Pages;

use App\Filament\Resources\BranchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBranch extends EditRecord
{
    protected static string $resource = BranchResource::class;
<<<<<<< HEAD
=======
    
    public $latitude;
    public $longitude;
>>>>>>> 28095241346b047a3ed5b77266e70574ffa1bf35

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
<<<<<<< HEAD
=======
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->latitude = $data['latitude'] ?? 24.7136;
        $this->longitude = $data['longitude'] ?? 46.6753;
        
        return $data;
    }
>>>>>>> 28095241346b047a3ed5b77266e70574ffa1bf35
}
