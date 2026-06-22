<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
   public function user() {
    return $this->belongsTo(User::class);
}
public function getCommentAttribute()
{
    $locale = app()->getLocale();
    return $this->{"comment_{$locale}"};
}
}
