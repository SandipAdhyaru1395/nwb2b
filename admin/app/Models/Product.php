<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'name',
        'sku',
        'description',
        'parent_product',
        'product_type',
        'price',
        'cost_price',
        'image_url',
        'wallet_credit',
        'stock_quantity',
        'notification_request_count',
        'min_order_quantity',
        'brand_id',
        'is_active',
        'is_new',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, ProductCategory::class, 'product_id', 'category_id');
    }
}
