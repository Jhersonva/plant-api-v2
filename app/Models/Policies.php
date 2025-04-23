<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Policies extends Model
{
    protected $table = 'policies';
    
    protected $fillable = ['title', 'description'];
    
    protected $hidden = ['created_at', 'updated_at'];

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageble');
    }

    protected static function booted()
    {
        static::deleting(function (Policies $policies) {
            $policies->image()->delete();
        });
    }
}
