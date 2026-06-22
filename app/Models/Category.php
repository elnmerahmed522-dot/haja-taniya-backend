<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    //ترجمه
    public function getNameAttribute()
{
    $locale = app()->getLocale(); // تجلب لغة التطبيق الحالية ('ar' أو 'en')
    return $this->{"name_{$locale}"};
}
}
