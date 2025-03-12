<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Purchase;
use Carbon\Carbon;

class PurchaseStatsWidget extends BaseWidget
{
    protected function getCards(): array
    {
        $thisMonth = Purchase::whereBetween('created_at', [
            Carbon::now()->startOfMonth(), 
            Carbon::now()->endOfMonth()
        ]);

        return [
            Card::make('Total Purchases', $thisMonth->count())
                ->description('Purchases This Month')
                ->descriptionIcon('heroicon-m-shopping-cart'),
            
            Card::make('Total Purchase Value', 'Rp ' . number_format($thisMonth->sum('total_price'), 2))
                ->description('Total Value This Month')
                ->descriptionIcon('heroicon-m-currency-dollar'),
            
            Card::make('Average Purchase Price', 'Rp ' . number_format($thisMonth->avg('total_price'), 2))
                ->description('Average Price This Month')
                ->descriptionIcon('heroicon-m-chart-bar'),
        ];
    }
}