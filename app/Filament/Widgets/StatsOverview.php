<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Category;
use App\Models\salesItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';
    protected function getStats(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        return [

            Stat::make('Total Products', Product::count()),
            Stat::make('Total Low Stock Products', Product::where('stock', '<', 10)->count()),
            Stat::make('Total Products Expiring in a Week', Product::where('expired_at', '<', Carbon::today()->addWeek())->where('expired_at', '>=', Carbon::today())->count()),
            Stat::make('Total Expired Products', Product::where('expired_at', '<', Carbon::today())->count()),
            Stat::make('Total Products Sold in this month', salesItem::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('quantity')), // Produk terjual di bulan ini
            Stat::make('Total Revenue in This Month', fn() => 'IDR ' . number_format(Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('grand_total'), 2)), // Total revenue bulan ini

        ];
    }
}
