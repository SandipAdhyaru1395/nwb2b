<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $table = 'brands';

    protected $fillable = [
        'name',
        'is_new',
        'is_hot',
        'is_sale',
        'is_active',
        'image',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'brand_category', 'brand_id', 'category_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_brand', 'brand_id', 'product_id');
    }
}
