<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Product extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'user_id',
        'brand_id',
        'category_id',
        'supplier_id',
        'name',
        'code',
        'variant',
        'description',
        'is_show',
        'photo',
        'price',
        'current_stock'
    ];

    public function stocks()
    {
        return $this->hasMany(StockPrice::class);
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function latestStockRecord()
    {
        return $this->hasOne(StockPrice::class, 'product_id')->latest('created_at');
    }
}
