<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PurchaseLineChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Monthly Purchases';

    protected function getData(): array
    {
        $purchases = Purchase::select(
            DB::raw('strftime("%m", created_at) as month'),
            DB::raw('strftime("%Y", created_at) as year'),
            DB::raw('SUM(total_price) as total_value'),
            DB::raw('COUNT(*) as total_purchases')
        )
        ->groupBy('year', 'month')
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get();

        return [
            'labels' => $purchases->map(function ($purchase) {
                return Carbon::create()->month((int)$purchase->month)->format('F');
            }),
            'datasets' => [
                [
                    'label' => 'Total Purchase Value',
                    'data' => $purchases->pluck('total_value'),
                    'borderColor' => 'rgb(75, 192, 192)',
                ],
                [
                    'label' => 'Number of Purchases',
                    'data' => $purchases->pluck('total_purchases'),
                    'borderColor' => 'rgb(255, 99, 132)',
                ]
            ]
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}