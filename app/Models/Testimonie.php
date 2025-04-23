<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Testimonie extends Model
{
    protected $fillable = [
        'name_customer',
        'description',
        'date',
        'qualification'
    ];

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageble');
    }

    protected static function booted()
    {
        static::deleting(function (Testimonie $testimonie) {
            $testimonie->image()->delete();
        });
    }
}
