<?php

namespace App\Filament\Exports;

use App\Models\Sale;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class SaleExporter extends Exporter
{
    protected static ?string $model = Sale::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('created_at')
            ->label('Transaction Date'),
            ExportColumn::make('customer_name')
            ->label('Customer Name'),
            ExportColumn::make('items.product.name')
            ->label('Products'),
            ExportColumn::make('items.quantity')
            ->label('Quantity'),
            ExportColumn::make('items.unit_amount')
            ->label('Unit Price'),
            ExportColumn::make('items.total_amount')
            ->label('Total Price'),
            ExportColumn::make('sub_total')
            ->label('Sub Total'),
            ExportColumn::make('discount_percentage')
            ->label('Discount Percentage'),
            ExportColumn::make('discount_amount')
            ->label('Discount Amount'),
            ExportColumn::make('tax_percentage')
            ->label('Tax Percentage'),
            ExportColumn::make('tax_amount')
            ->label('Tax Amount'),
            ExportColumn::make('grand_total')
            ->label('Grand Total'),
            ExportColumn::make('payment_method')
            ->label('Payment Method'),
            ExportColumn::make('notes')
            ->label('Notes'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your sale export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
