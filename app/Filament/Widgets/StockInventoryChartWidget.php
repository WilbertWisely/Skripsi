<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Stock;

class StockInventoryChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Inventory Breakdown';

    protected function getData(): array
    {
        $stocks = Stock::all();

        return [
            'labels' => $stocks->pluck('item_name'),
            'datasets' => [
                [
                    'label' => 'Quantity',
                    'data' => $stocks->pluck('qty'),
                    'backgroundColor' => $stocks->map(function () {
                        return 'rgb(' . rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ')';
                    }),
                ]
            ]
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}