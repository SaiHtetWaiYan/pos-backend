<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'buying_price',
        'selling_price',
        'stock',
        'reason'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
