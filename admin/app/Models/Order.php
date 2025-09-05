<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    
    protected $fillable = [
        'order_number',
        'total',
        'units_count',
        'skus_count',
        'items_count',
        'status',
        'payment_status'
    ];
}
