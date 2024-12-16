<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Models\Sale;
use Filament\Actions;
use Filament\Pages\Actions\Action;
use App\Filament\Resources\SaleResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Action::make('View Invoice')
            ->url(fn (Sale $record): string => route('preview-invoice', $record))
            ->openUrlInNewTab(),
            Action::make('Download Invoice')
            ->label('Download Invoice')
            ->url(fn (Sale $record): string => route('download-invoice', $record))
            ->openUrlInNewTab()
        ];
    }
}
