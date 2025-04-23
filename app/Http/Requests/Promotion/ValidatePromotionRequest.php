<?php

declare(strict_types=1);

namespace App\Http\Requests\Promotion;

use Illuminate\Http\Request;

trait ValidatePromotionRequest
{
    public function validatePromotionRequest(Request $request): void
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'image' => 'required|string'
        ]);
    }
}