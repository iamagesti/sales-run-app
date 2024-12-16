<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $fillable =[
        'customer_name',
        'grand_total',
        'payment_method',
        'notes',
        'user_id',
        'paid_amount',
        'change_amount',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
    ];
    public function user()
{
    return $this->belongsTo(User::class);
}


    public function items(){
        return $this->hasMany(salesItem::class);
    }
    public function calculateTotalPrice(): float
    {
        $totalPrice = 0;
        foreach ($this->items as $item) {
            $totalPrice += $item->quantity * $item->unit_price;
        }
        return $totalPrice;
    }
}
