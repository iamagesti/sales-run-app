<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\salesItem;
use Filament\Widgets\ChartWidget;

class ProductsChart extends ChartWidget
{
    protected static ?string $heading = 'Top 10 Best Seller Products';
    protected static ?int $sort = 3;
    protected function getData(): array
    {
         $topProducts = salesItem::selectRaw('product_id, SUM(quantity) as total_quantity')
         ->groupBy('product_id')
         ->orderByDesc('total_quantity')
         ->limit(10)
         ->get();


        $productNames = Product::whereIn('id', $topProducts->pluck('product_id'))
         ->pluck('name', 'id');


        $labels = $topProducts->map(function($item) use ($productNames) {
         return $productNames[$item->product_id];
     })->toArray();

     $data = $topProducts->pluck('total_quantity')->toArray();
        return [
            'datasets' => [
                [
                    'label' => 'Top 10 Best Seller Products',
                    'data' => $data,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1,
                ]
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
