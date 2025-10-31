<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\RecordsSyncUpdate;

class Product extends Model
{
    use SoftDeletes;
    use RecordsSyncUpdate;
    protected $table = 'products';

    protected $fillable = [
        'name',
        'sku',
        'description',
        'price',
        'cost_price',
        'image_url',
        'wallet_credit',
        'stock_quantity',
        'step_quantity',
        'notification_request_count',
        'vat_amount',
        'vat_method_name',
        'vat_method_type',
        'is_active',
    ];

    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'product_brand', 'product_id', 'brand_id');
    }
}
