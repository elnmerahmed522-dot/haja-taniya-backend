<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function category() {
    return $this->belongsTo(Category::class);
}
public function images() {
    return $this->hasMany(ProductImage::class);
}
public function sizes() {
    return $this->belongsToMany(Size::class);
}

public function colors() {
    return $this->belongsToMany(Color::class);
}

public function getTitleAttribute()
{
    $locale = app()->getLocale();
    return $this->{"title_{$locale}"};
}

public function getDescriptionAttribute()
{
    $locale = app()->getLocale();
    return $this->{"description_{$locale}"};
}
}
