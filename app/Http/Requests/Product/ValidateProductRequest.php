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
}