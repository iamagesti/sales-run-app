<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use App\Filament\Resources\ProductResource;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpiredProductThisWeek extends BaseWidget
{
    protected static ?string $heading = 'Products Expired In a Week';
    protected static ?int $sort = 6;
    public function table(Table $table): Table
    {
        return $table
            ->query(ProductResource::getEloquentQuery()->where('expired_at', '<', Carbon::today()->addWeek())->where('expired_at', '>=', Carbon::today()))
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
