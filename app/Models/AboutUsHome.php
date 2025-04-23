<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutUsHome extends Model
{
    use HasFactory;

    protected $table = 'about_us_home';

    protected $fillable = [
        'text_section_one',
        'text_section_two',
    ];

    public function images()
    {
        return $this->morphOne(Image::class, 'imageble');
    }

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected static function booted()
    {
        static::deleting(function (AboutUsHome $aboutUsHome) {
            $aboutUsHome->images()->delete();
        });
    }
}
