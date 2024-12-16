<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class salesItem extends Model
{
    use HasFactory;

    protected $fillable =[
        'sale_id',
        'product_id',
        'quantity',
        'unit_amount',
        'total_amount',
        'paid_amount',
        'change_amount',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
    ];

    public function sale(){
        return $this->belongsTo(Sale::class);
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function getProductNameAttribute()
    {
        return $this->product ? $this->product->name : null;
    }

    protected static function boot()
    {
        parent::boot();

        // Mengurangi stok saat item penjualan dibuat
        static::created(function ($salesItem) {
            $product = $salesItem->product;
            if ($product) {
                $product->decrement('stock', $salesItem->quantity);
            }
        });

        // Menyesuaikan stok saat item penjualan diperbarui
        static::updated(function ($salesItem) {
            $originalQuantity = $salesItem->getOriginal('quantity');
            $newQuantity = $salesItem->quantity;

            if ($newQuantity != $originalQuantity) {
                $product = $salesItem->product;
                if ($product) {
                    $product->increment('stock', $originalQuantity - $newQuantity);
                }
            }
        });

        // Mengembalikan stok saat item penjualan dihapus
        static::deleted(function ($salesItem) {
            $product = $salesItem->product;
            if ($product) {
                $product->increment('stock', $salesItem->quantity);
            }
        });
    }

}
