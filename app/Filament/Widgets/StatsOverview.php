<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatusEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    // override refresh
    protected static ?string $pollingInterval = '15s';
    //lazy load default is true
    protected static ?string $islazy = 'false';
    protected static ?int $sort = 2;
    protected function getStats(): array
    {
        return [
            stat::make('Total customers', Customer::count())
                ->description('increase  in customers')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 3, 5, 6, 3, 2, 1, 4]),
            Stat::make('Totale Product', Product::count())
                ->description('Total products in app')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart([7, 3, 5, 6, 3, 2, 1, 4]),
            Stat::make('pending orders', Order::where('status', OrderStatusEnum::PENDING->value)
                ->count())
                ->description('Total products in app')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart([7, 3, 5, 6, 3, 2, 1, 4]),
        ];
    }
}
