<?php
namespace App\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\FromCollection;

class PurchasesExport implements FromCollection
{
    public function collection()
    {
        return Purchase::all(); // Fetch all purchase records
    }
}
