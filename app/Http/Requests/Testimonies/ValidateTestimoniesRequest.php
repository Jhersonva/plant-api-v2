<?php

declare(strict_types=1);

namespace App\Http\Requests\Testimonies;

use Illuminate\Http\Request;

trait ValidateTestimoniesRequest
{
    public function validateTestimoniesRequest(Request $request): void
    {
        $request->validate([
            'name_customer' => 'required|string|max:256',
            'description' => 'required|string|max:256',
            'date' => 'required|string',
            'qualification' => 'required|numeric',
            'image' => 'nullable|string'
        ]);
    }
}