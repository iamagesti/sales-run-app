<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\ProductResource;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpiredProducts extends BaseWidget
{
    protected static ?string $heading = 'Products Expired';
    protected static ?int $sort = 5;
    public function table(Table $table): Table
    {
        return $table
            ->query(ProductResource::getEloquentQuery()->where('expired_at', '<', now()))
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name')
                ->sortable()
                ->searchable(),
                Tables\Columns\TextColumn::make('category.name'),
                Tables\Columns\TextColumn::make('expired_at')
                ->sortable()
                ->datetime(),
            ]);
    }
}
