<?php

declare(strict_types=1);

namespace App\Http\Requests\AboutUs;

use Illuminate\Http\Request;

trait ValidateAboutUs
{
    public function validateAboutUs(Request $request)
    {
        $request->validate([
            'mission' => 'nullable|string',
            'vision' => 'nullable|string',
            'name_yt' => 'nullable|string|max:100',
            'url_yt' => 'nullable|url'
        ]);
    }

}