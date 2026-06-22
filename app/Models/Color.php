<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $guarded = [];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
    public function getNameAttribute()
{
    $locale = app()->getLocale();
    return $this->{"name_{$locale}"};
}
}
