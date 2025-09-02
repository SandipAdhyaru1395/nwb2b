<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    protected$fillable = [
        'name',
        'description',
        'status',
    ];

    public function setDescriptionAttribute($value)
    {
        // Strip HTML tags and check if content is empty
        $clean = trim(strip_tags($value));
        // If content is empty or just <p><br></p>, store null
        $this->attributes['description'] = ($clean === '') ? null : $value;
    }
}
