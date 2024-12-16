<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class SaleChart extends ChartWidget
{
    protected static ?string $heading = 'Total Revenue Per Month';
    protected static ?int $sort = 2;
    protected function getData(): array
    {
        $data = $this->getSalesPerMonth();
        return [
            'datasets' => [
                [
                   'label' => 'Total Revenue Per Month',
                    'data' => $data['salesPerMonth'],
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1,
                ]
                ],
                'labels' => $data['months'],

                ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function getSalesPerMonth() {
              $salesData = Sale::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, sum(grand_total) as total')
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        $salesPerMonth = $salesData->pluck('total')->toArray();
        $months = $salesData->pluck('month')->map(function($date) {
        return Carbon::parse($date)->format('M');
        })->toArray();
        return [
            'salesPerMonth' => $salesPerMonth,
            'months' => $months
        ];
    }
}
