<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable =[
        'category_id',
        'name',
        'image',
        'description',
        'initial_price',
        'selling_price',
        'stock',
        'is_active',
        'barcode',
        'expired_at'
    ];

    protected $appends = ['image_url'];
    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function salesItem(){
        return $this->hasMany(salesItem::class);
    }


    public function getImageUrlAttribute()
{

    return $this->image ? url('storage/' . $this->image) : null;
}


    protected $casts = [
        'initial_price' => 'integer',
        'selling_price' => 'integer',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'product_id' => 'array',
        //'image' => 'array',
    ];
    public function scopeSearch($query, $search) {
        return $query->where('name', 'like', '%' . $search . '%')
                     ->orWhere('barcode', 'like', '%' . $search . '%');
    }


}
