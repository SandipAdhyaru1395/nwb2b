<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    protected $table = 'categories';

    protected $fillable = ['name', 'parent_id', 'description','is_active','sort_order'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_categories', 'category_id', 'product_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class,'parent_id')->with('children');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class,'parent_id');
    }

    /**
     * Recursively eager-load children categories.
     */
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    /**
     * Brands directly attached to this category via pivot.
     */
    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'brand_category', 'category_id', 'brand_id');
    }
}
