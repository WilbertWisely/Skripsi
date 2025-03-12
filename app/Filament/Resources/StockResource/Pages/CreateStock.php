<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStock extends CreateRecord
{
    protected static string $resource = StockResource::class;
 function afterCreate(): void
    {
        $this->redirect(StockResource::getUrl('index'));
    }
    protected function getRedirectUrl(): string
    {
        return StockResource::getUrl('index');  // Always redirect to index
    }
}
