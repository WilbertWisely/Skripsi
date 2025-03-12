<?php

namespace App\Filament\Resources\TreatmentResource\Pages;

use App\Filament\Resources\TreatmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTreatment extends CreateRecord
{
    protected static string $resource = TreatmentResource::class;

    protected function afterCreate(): void
    {
        $this->redirect($this->getResource()::getUrl('index'));
    }
    protected function getRedirectUrl(): string
    {
        return TreatmentResource::getUrl('index');  // Always redirect to index
    }
}

