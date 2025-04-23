<?php

declare(strict_types =1);

namespace App\Http\Requests\Subcategory;

use Illuminate\Http\Request;

trait ValidateSubcategoryRequest
{
    public function validateSubcategoryRequest(Request $request): void
    {
        $request->validate([
            'name' => 'required|string|unique:subcategories,name'
        ]);
    }
}