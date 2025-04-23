<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission', 
        'vision', 
        'about_values', 
        'name_yt', 
        'url_yt'
    ];

    public function images()
    {
        return $this->morphOne(Image::class, 'imageble');
    }

    protected $casts = [
        'about_values' => 'array',
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected static function booted()
    {
        static::deleting(function (AboutUs $aboutUs) {
            $aboutUs->images()->delete();
        });
    }
}
