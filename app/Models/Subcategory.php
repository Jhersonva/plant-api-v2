<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subcategory extends Model
{
    protected $fillable = [
        'name',
        'category_id'
    ];
    protected $hidden = [
        'pivot',
        'category_id',
        'created_at',
        'updated_at'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function product(): BelongsToMany
    {
        return $this->belongsToMany(Subcategory::class, 'product_subcategory');
    }
}
