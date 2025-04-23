<?php

declare(strict_types=1);

namespace App\Http\Requests\AboutUsHome;

use Illuminate\Http\Request;

trait ValidateAboutUsHome
{
    public function validateAboutUsHome(Request $request)
    {
        $request->validate([
            'text_section_one' => 'nullable|string',
            'text_section_two' => 'nullable|string',
        ]);
    }

}