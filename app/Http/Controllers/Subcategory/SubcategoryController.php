<?php

namespace App\Http\Controllers\Subcategory;

use App\Exceptions\Category\NotFoundCategory;
use App\Exceptions\Subcategory\NotFoundSubcategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subcategory\ValidateSubcategoryRequest;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    use ValidateSubcategoryRequest;

    /**
 * @OA\Post(
 *     path="/api/categories/{nameCategory}/subcategories",
 *     summary="Registrar una nueva subcategoría dentro de una categoría",
 *     tags={"Subcategories"},
 *     @OA\Parameter(
 *         name="nameCategory",
 *         in="path",
 *         required=true,
 *         description="Nombre de la categoría a la que pertenece la subcategoría",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="name", type="string", example="Hortalizas")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Subcategoría registrada exitosamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="Subcategoría registrada")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Categoría no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Categoría no encontrada")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error en la validación de los datos",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="El campo name es requerido")
 *         )
 *     )
 * )
 */
    public function storeSubcategory(string $nameCategory, Request $request): JsonResponse
    {
        $category = Category::where('name', $nameCategory)->first();
        if (!$category) {
            throw new NotFoundCategory;
        }
        $this->validateSubcategoryRequest($request);
        $category->subcategories()->create([
            'name' => $request->name
        ]);
        return new JsonResponse(['data' => 'Subcategotria registrada']);
    }

    /**
 * @OA\Delete(
 *     path="/api/subcategories/{nameSubcategory}",
 *     summary="Eliminar una subcategoría",
 *     tags={"Subcategories"},
 *     @OA\Parameter(
 *         name="nameSubcategory",
 *         in="path",
 *         required=true,
 *         description="Nombre de la subcategoría a eliminar",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Subcategoría eliminada exitosamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="Subcategoria eliminada")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Subcategoría no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Subcategoría no encontrada")
 *         )
 *     )
 * )
 */
    public function deleteSubcategory(string $nameSubcategory): JsonResponse
    {
        $nameSubcategory = Subcategory::where('name', $nameSubcategory)->first();
        if (!$nameSubcategory) {
            throw new NotFoundSubcategory;
        }
        $nameSubcategory->delete();
        return new JsonResponse(['data' => 'Subcategoria eliminada']);
    }

    /**
 * @OA\Get(
 *     path="/api/categories/{nameCategory}/subcategories",
 *     summary="Obtener todas las subcategorías de una categoría",
 *     tags={"Subcategories"},
 *     @OA\Parameter(
 *         name="nameCategory",
 *         in="path",
 *         required=true,
 *         description="Nombre de la categoría",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Lista de subcategorías obtenida exitosamente",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Frutas")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Categoría no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Categoría no encontrada"),
 *             @OA\Property(property="error", type="string", example="No se encontró la categoría especificada")
 *         )
 *     )
 * )
 */
    public function getAllSubcategories(string $nameCategory): JsonResponse
    {
        $category = Category::where('name', $nameCategory)->first();

        if (!$category) {
            throw new NotFoundCategory;
        }
        $subcategories = Subcategory::select('id', 'name')
            ->where('category_id', $category->id)
            ->get();
        return new JsonResponse(['data' => $subcategories]);
    }
}
