<?php

declare(strict_types=1);

namespace App\Http\Requests\Blog;

use Illuminate\Http\Request;

trait ValidateBlogRequest
{
    public function validateBlogRequest(Request $request): void
    {
        $request->validate([
            'title' => 'required|string|max:256',
            'description' => 'required|string',
            'category_id' => 'required|integer|exists:categories,id',
            'image' => 'string|nullable'
        ]);
    }
}