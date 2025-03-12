<?php
namespace App\Exports;

use App\Models\Sales;
use Maatwebsite\Excel\Concerns\FromCollection;

class SalesExport implements FromCollection
{
    public function collection()
    {
        return Sales::all(); // Fetch all purchase records
    }
}
