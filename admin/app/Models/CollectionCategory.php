<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectionCategory extends Model
{
    protected $table = 'collection_category';

    protected$fillable = [
        'collection_id',
        'category_id',
        'is_primary',
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }
}
