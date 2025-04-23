<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use Illuminate\Http\Request;

trait ValidateProductRequest
{
    public function validateProducRequest(Request $request): void
    {
        $request->validate([
            'name' => 'required|string|unique:products,name|max:256',
            'characteristics' => 'required|string|max:10000',
            'benefits' => 'required|array',
            'compatibility' => 'required|string|max:10000',
            'price' => 'required|numeric|min:1|max:999999.99',
            'stock' => 'required|integer|min:0|max:10000',
            'subcategory_id' => 'required|array',
            'subcategory_id.*' => 'integer|exists:subcategories,id',
            'image' => 'string|nullable'
        ],);
    }
}