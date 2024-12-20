<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\Product;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use App\Filament\Resources\ProductResource;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStocks extends BaseWidget
{
    protected static ?string $heading = 'Low Stocks Products';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    public function table(Table $table): Table
    {

        return $table
            ->query(ProductResource::getEloquentQuery()->where('stock', '<', 10))
            ->defaultSort('name','stock')
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name')
                ->sortable()
                ->searchable(),
                Tables\Columns\TextColumn::make('category.name'),
                Tables\Columns\TextColumn::make('stock')
                ->sortable(),

            ]);
    }
}
