<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ProductChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';
    protected static ?int $sort = 3;


    protected function getData(): array
    {
        $data = $this->getProductsPerMonth();

        return [
            'datasets' => [
                [
                    'label' => 'Products created per month',
                    'data' => $data['productsPerMonth']
                ],
            ],
            'labels' => $data['months'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    private function getProductsPerMonth(): array
    {
        $now = Carbon::now();
        $productsPerMonth = [];

        $months = collect(range(1, 12))->map(function ($month) use ($now, &$productsPerMonth) {
            $startOfMonth = $now->copy()->month($month)->startOfMonth();
            $endOfMonth = $now->copy()->month($month)->endOfMonth();
            $count = Product::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

            $productsPerMonth[] = $count;

            return $startOfMonth->format('M');
        })->toArray();
        return [
            'productsPerMonth' => $productsPerMonth,
            'months' => $months
        ];
    }
}
