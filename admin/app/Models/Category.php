<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    protected $table = 'categories';

    protected $fillable = ['name', 'parent_id', 'description', 'image_url','is_active','sort_order'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_categories', 'category_id', 'product_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class,'parent_id');
    }
}
