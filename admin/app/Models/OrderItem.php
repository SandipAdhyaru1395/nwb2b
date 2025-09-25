<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
	use SoftDeletes;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'wallet_credit_earned',
        'total_price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
