<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'wallet_credit_earned',
        'total_price',
    ];
}
