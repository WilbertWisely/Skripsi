<?php

namespace App\Exports;

use App\Models\Stock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StocksExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Stock::all(); // Or filter as needed
    }

    public function headings(): array
    {
        return [
            'ID', 'Item Name', 'Unit', 'Quantity', 'Buy Price', 'Sell Price', 'Created At', 'Updated At'
        ];
    }
}
