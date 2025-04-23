<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use Illuminate\Http\Request;

trait ValidateCustomerRequest
{
    public function validateCustomerRequest(Request $request): void
    {
        $request->validate([
            'email' => 'required|email',
            'active' => 'required|boolean'
        ]);
    }
}