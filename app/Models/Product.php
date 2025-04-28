<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'status',
        'category_id',
    ];

    protected $casts = [
        'price' => 'float',
    ];

    protected $hidden = [
        'pdf_id',
        'category_id', 
    ];

    public function pdf(): BelongsTo
    {
        return $this->belongsTo(Pdf::class, 'pdf_id');
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageble');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategories()
    {
        return $this->belongsToMany(SubCategory::class, 'product_subcategory', 'product_id', 'subcategory_id');
    }

    protected static function booted()
    {
        static::deleting(function (Product $product) {
            $product->image()->delete();
        });
    }
}
