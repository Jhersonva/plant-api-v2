<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Blog extends Model
{
    protected $fillable = [
        'title',
        'description',
        'category_id',
        'date'
    ];
    protected $hidden = [
        'category_id'
    ];

    public function category():BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageble');
    }

    protected static function booted()
    {
        static::deleting(function (Blog $blog) {
            $blog->image()->delete();
        });
    }
}
