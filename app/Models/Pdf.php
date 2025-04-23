<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pdf extends Model
{
    protected $fillable = [
        'url',
        'datetime'
    ];

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'product_id');
    }
}
