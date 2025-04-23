<?php

declare(strict_types=1);

namespace App\Http\Requests\Policy;

use Illuminate\Http\Request;

trait ValidatePolicyUpdate
{
    public function validatePolicyUpdate(Request $request)
    {
        return $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:10000'
        ]);
    }
}