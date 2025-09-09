<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'name',
        'sku',
        'collection_id',
        'description',
        'price',
        'cost_price',
        'image_url',
        'wallet_credit',
        'stock_quantity',
        'notification_request_count',
        'min_order_quantity',
        'is_active',
    ];

   
}
