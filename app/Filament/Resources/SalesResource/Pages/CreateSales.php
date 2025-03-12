<?php

namespace App\Filament\Resources\SalesResource\Pages;

use App\Filament\Resources\SalesResource;
use App\Models\Sales;
use App\Models\SalesItem;
use App\Models\SalesTreatments;
use App\Models\Stock;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateSales extends CreateRecord
{
    protected static string $resource = SalesResource::class;

    protected function getRedirectUrl(): string
    {
        return SalesResource::getUrl('index');  // Always redirect to index
    }
    

    protected function afterSave(): void
    {
 // Get the created sale
 $sales = $this->record;

 // Detailed logging of the entire data
 Log::channel('daily')->info('Sales Creation Attempt', [
     'sales_id' => $sales->id,
     'full_data' => $this->data,
 ]);

 // Log salesItems
 if (isset($this->data['salesItems'])) {
     Log::channel('daily')->info('Sales Items Data', [
         'sales_items_count' => count($this->data['salesItems']),
         'sales_items' => $this->data['salesItems'],
     ]);
 }

 // Log salesTreatments
 if (isset($this->data['salesTreatments'])) {
     Log::channel('daily')->info('Sales Treatments Data', [
         'sales_treatments_count' => count($this->data['salesTreatments']),
         'sales_treatments' => $this->data['salesTreatments'],
     ]);
 }
     @dd($this->data['salesitems']);
        // Add Sales Items (Products)
        foreach ($this->data['salesItems'] as $itemData) {
            // Create SalesItem
            $sales->salesItems()->create([
                'stock_id' => $itemData['stock_id'],
                'qty' => $itemData['qty'],
                'price' => $itemData['price'],
            ]);

            // Update stock
            $stock = Stock::find($itemData['stock_id']);
            $stock->decrement('qty', $itemData['qty']);
        }

        // Add Sales Treatments
        foreach ($this->data['salesTreatments'] as $treatmentData) {
            // Create SalesTreatments
            $sales->salesTreatments()->create([
                'treatment_id' => $treatmentData['treatment_id'],
                'quantity' => $treatmentData['quantity'],
                'unit_price' => $treatmentData['unit_price'],
            ]);
        }

        
        // Call the parent afterSave
        parent::afterSave();
    }
}
