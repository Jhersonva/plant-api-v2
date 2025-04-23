<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Product extends Model
{
    protected $fillable = [
        'name',
        'characteristics',
        'benefits',
        'compatibility',
        'price',
        'stock',
        'pdf_id',
        'status'
    ];
    protected $hidden = [
        'pdf_id'
    ];

    public function pdf(): BelongsTo
    {
        return $this->belongsTo(Pdf::class, 'pdf_id');
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageble');
    }

    public function subCategories(): BelongsToMany
    {
        return $this->belongsToMany(Subcategory::class, 'product_subcategory');
    }

    protected static function booted()
    {
        static::deleting(function (Product $product) {
            $product->image()->delete();
        });
    }
}
