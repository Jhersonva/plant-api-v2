<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use Illuminate\Http\Request;

trait ValidateProductRequest
{
    public function validateProducRequest(Request $request): void
    {
        $request->validate([
            'name' => 'required|string|max:256|unique:products,name,' . $request->id, // Permite que el nombre del mismo producto no sea conflictivo
            'characteristics' => 'required|string|max:10000',
            'benefits' => 'required|array',
            'compatibility' => 'required|string|max:10000',
            'price' => 'required|numeric|min:1|max:999999.99',
            'stock' => 'required|integer|min:0|max:10000',
            'image' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|array|min:1',
            'subcategory_id.*' => 'exists:subcategories,id',
        ]);
    
        // Validar que las subcategorías pertenezcan a la categoría seleccionada
        if ($request->has('subcategory_id') && $request->has('category_id')) {
            $subcategories = \App\Models\Subcategory::whereIn('id', $request->subcategory_id)
                ->pluck('category_id')
                ->unique();
    
            if ($subcategories->count() > 1 || $subcategories->first() != $request->category_id) {
                abort(422, 'Todas las subcategorías deben pertenecer a la categoría seleccionada.');
            }
        }
    }

    public function validatePartialProductRequest(Request $request): void
    {
        $request->validate([
            'name' => ['sometimes', 'string', 'max:256'],
            'characteristics' => ['sometimes', 'string', 'max:10000'],
            'benefits' => ['sometimes', 'array'],
            'benefits.*' => ['sometimes', 'string', 'max:255'], // Cada beneficio individual
            'compatibility' => ['sometimes', 'string', 'max:10000'],
            'price' => ['sometimes', 'numeric', 'min:1', 'max:999999.99'],
            'stock' => ['sometimes', 'integer', 'min:0', 'max:10000'],
            'image' => ['sometimes', 'nullable', 'array'],
            'image.url' => ['required_with:image', 'string'],
            'image.id' => ['nullable', 'integer'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'subcategory_id' => ['sometimes', 'array', 'min:1'],
            'subcategory_id.*' => ['sometimes', 'exists:subcategories,id'],
            'pdf' => ['sometimes', 'nullable', 'string'], // Base64 o url
        ]);

        // Validar que las subcategorías pertenezcan a la categoría seleccionada
        if ($request->has('subcategory_id') && $request->has('category_id')) {
            $subcategories = \App\Models\Subcategory::whereIn('id', $request->subcategory_id)
                ->pluck('category_id')
                ->unique();

            if ($subcategories->count() > 1 || $subcategories->first() != $request->category_id) {
                abort(422, 'Todas las subcategorías deben pertenecer a la categoría seleccionada.');
            }
        }
    }
}