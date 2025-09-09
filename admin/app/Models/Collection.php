<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $table = 'collections';

    protected $fillable = [
        'name',
        'is_new',
        'is_hot',
        'is_sale',
        'is_active',
        'brand_id',
        'image',
    ];

     public function categories()
    {
        return $this->belongsToMany(Category::class, CollectionCategory::class, 'collection_id', 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
