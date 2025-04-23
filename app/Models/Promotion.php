<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Promotion extends Model
{
    protected $fillable = [
        'title',
        'description',
    ];

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageble');
    }

    protected static function booted()
    {
        static::deleting(function (Promotion $promotion) {
            $promotion->image()->delete();
        });
    }
}
