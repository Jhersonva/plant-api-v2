<?php

declare(strict_types=1);

namespace App\Http\utils\Product;

use App\Models\Product;

trait FindProductExists
{
    public function findProductExists(string $nameProduct): bool
    {
        $product = Product::where('name', $nameProduct)->first();
        if($product){
            return true;
        }
        return false;
    }
}