<?php

declare(strict_types=1);

namespace App\Http\Requests\Category;

use Illuminate\Http\Request;

trait ValidateCategoryRequest
{
    public function validateCategoryRequest(Request $request): void
    {
        $request->validate([
            'name' => 'required|string|unique:categories,name'
        ]);
    }
}