<?php

namespace App\Filament\Resources\SalesResource\Pages;

use App\Filament\Resources\SalesResource;
use App\Models\Sales;
use App\Models\SalesItem;
use App\Models\Stock;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSales extends EditRecord
{
    protected static string $resource = SalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->redirect($this->getResource()::getUrl('index'));
    }

    protected function afterDelete(): void
    {
        $sales = $this->record;

        // Restore stock quantities after sale is deleted
        foreach ($sales->salesItems as $item) {
            $stock = $item->stock;
            $stock->increment('qty', $item->qty); // Restore stock quantity
        }

        // Call the parent afterDelete
        parent::afterDelete();
    }
}
