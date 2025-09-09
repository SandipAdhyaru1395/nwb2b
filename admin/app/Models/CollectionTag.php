<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectionTag extends Model
{
    protected $table = 'collection_tag';

    protected $fillable = [
        'collection_id',
        'tag_id',
    ];
}
