<?php

declare(strict_types=1);

namespace App\Http\Requests\Servics;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait ValidateServiceStore
{
    public function validateServiceStore(Request $request) : array
    {
        try {
            return $request->validate([
                'title' => 'required|string|min:3|unique:services,title',
                'description' => 'required|string|min:5',
                'features' => 'sometimes|array'
            ]);
        } catch (ValidationException $e) {
            throw new HttpResponseException(response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422));
        }
    }

    public function validateServiceUpdate(Request $request) : array
    {
        return $request->validate([
            'title' => 'sometimes|string|min:3',
            'description' => 'sometimes|string|min:5',
            'features' => 'sometimes|array'
        ]);
    }
}