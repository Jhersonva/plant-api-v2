<?php

namespace App\Http\Controllers\Category;

use App\Exceptions\Category\NotFoundCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\ValidateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ValidateCategoryRequest;
    /**
     * @OA\Post(
     *     path="/api/categories",
     *     summary="Registrar una nueva categoría",
     *     tags={"Categories"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Verduras")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Categoría registrada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Categoria registrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error en la validación de los datos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         enum={
     *                             "El campo name es requerido",
     *                             "The name has already been taken."
     *                         }
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function storeCategory(Request $request): JsonResponse
    {
        $this->validateCategoryRequest($request);
        Category::create([
            'name' => $request->name
        ]);
        return new JsonResponse(['data' => 'Categoria registrada']);
    }
    /**
     * @OA\Put(
     *     path="/api/categories/{id}",
     *     summary="Actualizar una categoría",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la categoría a actualizar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Frutas")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoría actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Categoria actualizada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoría no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Categoría no encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error en la validación de los datos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         enum={
     *                             "El campo name es requerido",
     *                             "The name has already been taken."
     *                         }
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function updateCategory(int $id, Request $request): JsonResponse
    {
        $this->validateCategoryRequest($request);
        $category = Category::find($id);
        if (!$category) {
            throw new NotFoundCategory;
        }
        $category->update([
            'name' => $request->name
        ]);
        return new JsonResponse(['data' => 'Categoria actualizado']);
    }
    /**
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     summary="Eliminar una categoría",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la categoría a eliminar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoría eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Categoria eliminada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoría no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Categoría no encontrada")
     *         )
     *     )
     * )
     */
    public function deleteCategory(int $id): JsonResponse
    {
        $category = Category::find($id);
        if (!$category) {
            throw new NotFoundCategory;
        }
        $category->delete();
        return new JsonResponse(['date' => 'Categoria eliminada']);
    }
    /**
/**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Obtener todas las categorías",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="subcategory",
     *         in="query",
     *         required=false,
     *         description="Filtrar categorías por nombre de subcategoría",
     *         @OA\Schema(type="string", example="Acidas")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         required=false,
     *         description="Filtrar nombres de categorias",
     *         @OA\Schema(type="string", example="Frutas")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de categorías obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Frutas"),
     *                     @OA\Property(property="subcategories", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer", example=10),
     *                             @OA\Property(property="name", type="string", example="Cítricos"),
     *                             @OA\Property(property="category_id", type="integer", example=1)
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getAllCategories(Request $request): JsonResponse
    {
        $nameSubcategory = $request->query('subcategory');
        $nameCategory = $request->query('category');
        $query = Category::select('id', 'name');

        if ($nameCategory) {
            $query->where('name', 'like', "%{$nameCategory}%");
        }

        if ($nameSubcategory) {
            $query->whereHas('subcategories', function ($query) use ($nameSubcategory) {
                $query->where('name', 'like', "%{$nameSubcategory}%");
            });
        }
        $categories = $query->with(['subcategories' => function ($query) use ($nameSubcategory) {
            if ($nameSubcategory) {
                $query->where('name', 'like', "%{$nameSubcategory}%");
            }
            $query->select('id', 'name', 'category_id');
        }])->get();
        return new JsonResponse(['data' => $categories]);
    }
}
