<?php

namespace App\Filament\Resources\BranchResource\Pages;

use App\Filament\Resources\BranchResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBranch extends CreateRecord
{
    protected static string $resource = BranchResource::class;
    protected function mutateFormDataBeforeSave(array $data): array
    {
       dd('mutateFormDataBeforeSave called', $data);
        if (auth()->user()?->school_id) {
            $data['school_id'] = auth()->user()->school_id;
        }
        return $data;
    }
    
    public $latitude = 24.7136;
    public $longitude = 46.6753;
}

