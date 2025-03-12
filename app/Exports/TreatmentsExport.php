<?php

namespace App\Exports;

use App\Models\Treatment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TreatmentsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Treatment::all(); // You can filter or specify the columns to export
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Category', 'Price'];
    }
}
