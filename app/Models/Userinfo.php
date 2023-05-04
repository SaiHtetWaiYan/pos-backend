<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userinfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'shop_name',
        'phone_number',
        'street_address',
        'city',
        'state',
        'country'
    ];
}
