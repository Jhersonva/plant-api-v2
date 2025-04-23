<?php

namespace App\Http\Controllers\Promotion;

use App\Exceptions\Promotion\NotFoundPromotion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Promotion\ValidatePromotionRequest;
use App\Http\Service\Image\DeleteImage;
use App\Http\Service\Image\SaveImage;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    use
        ValidatePromotionRequest,
        SaveImage,
        DeleteImage;

    /**
     * @OA\Post(
     *     path="/api/promotions",
     *     summary="Registrar una nueva promoción",
     *     tags={"Promotions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description", "image"},
     *             @OA\Property(property="title", type="string", example="Título de la promoción"),
     *             @OA\Property(property="description", type="string", example="Descripción de la promoción"),
     *             @OA\Property(property="image", type="string", format="base64", example="data:image/png;base64,iVBORw0KGgoAAAANS...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Promoción creada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Promocion creada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="title", type="array", @OA\Items(type="string", example="El título es requerido")),
     *                 @OA\Property(property="description", type="array", @OA\Items(type="string", example="La descripción es requerida")),
     *                 @OA\Property(property="image", type="array", @OA\Items(type="string", example="La imagen es requerida"))
     *             )
     *         )
     *     )
     * )
     */

    public function storePromotion(Request $request): JsonResponse
    {
        $this->validatePromotionRequest($request);
        $promotion = Promotion::create([
            'title' => $request->title,
            'description' => $request->description
        ]);
        $image = $this->saveImage($request->image, 'promotions');
        $promotion->image()->create([
            'url' => $image
        ]);
        return new JsonResponse(['data' => 'Promocion creada']);
    }

    /**
     * @OA\Put(
     *     path="/api/promotions/{promotionId}",
     *     summary="Actualizar una promoción existente",
     *     tags={"Promotions"},
     *     @OA\Parameter(
     *         name="promotionId",
     *         in="path",
     *         required=true,
     *         description="ID de la promoción a actualizar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description", "image"},
     *             @OA\Property(property="title", type="string", example="Nuevo título de la promoción"),
     *             @OA\Property(property="description", type="string", example="Nueva descripción de la promoción"),
     *             @OA\Property(property="image", type="string", format="base64", example="data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Promoción actualizada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Se actualizo la promoción")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Promoción no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Promoción no encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="title", type="array", @OA\Items(type="string", example="El título es requerido")),
     *                 @OA\Property(property="description", type="array", @OA\Items(type="string", example="La descripción es requerida")),
     *                 @OA\Property(property="image", type="array", @OA\Items(type="string", example="La imagen es requerida"))
     *             )
     *         )
     *     )
     * )
     */

    public function updatePromotion(Request $request, int $promotionId): JsonResponse
    {
        $promotion = Promotion::find($promotionId);
        if (!$promotion) {
            throw new NotFoundPromotion;
        }
        $this->validatePromotionRequest($request);
        $image = $this->saveImage($request->image, 'promotions');
        $this->deleteImage($promotion->image->url);
        $promotion->update([
            'title' => $request->title,
            'description' => $request->description
        ]);
        $promotion->image()->update([
            'url' => $image
        ]);
        return new JsonResponse(['data' => 'Se actualizo la promoción']);
    }

    /**
     * @OA\Delete(
     *     path="/api/promotions/{promotionId}",
     *     summary="Eliminar una promoción",
     *     tags={"Promotions"},
     *     @OA\Parameter(
     *         name="promotionId",
     *         in="path",
     *         required=true,
     *         description="ID de la promoción a eliminar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Promoción eliminada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Promocion eliminado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Promoción no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Promoción no encontrada")
     *         )
     *     )
     * )
     */

    public function deletePromotion(int $promotionId): JsonResponse
    {
        $promotion = Promotion::find($promotionId);
        if (!$promotion) {
            throw new NotFoundPromotion;
        }
        $this->deleteImage($promotion->image->url);
        $promotion->delete();
        return new JsonResponse(['data' => 'Promocion eliminado']);
    }

    /**
     * @OA\Get(
     *     path="/api/promotions",
     *     summary="Obtener todas las promociones",
     *     tags={"Promotions"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de promociones",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Título de la promoción"),
     *                 @OA\Property(property="description", type="string", example="Descripción de la promoción"),
     *                 @OA\Property(
     *                     property="image",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=10),
     *                     @OA\Property(property="imageble_id", type="integer", example=1),
     *                     @OA\Property(property="url", type="string", example="promotions/image.jpg")
     *                 )
     *             ))
     *         )
     *     )
     * )
     */

    public function getAllPromotions(): JsonResponse
    {
        $promotion  = Promotion::select(
            'id',
            'title',
            'description'
        )
            ->with([
                'image:id,imageble_id,url'
            ])->get();
        return new JsonResponse([
            'data' => $promotion
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/promotions/{promotionId}",
     *     summary="Obtener una promoción por ID",
     *     tags={"Promotions"},
     *     @OA\Parameter(
     *         name="promotionId",
     *         in="path",
     *         required=true,
     *         description="ID de la promoción",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Promoción encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Título de la promoción"),
     *                 @OA\Property(property="description", type="string", example="Descripción de la promoción"),
     *                 @OA\Property(property="image", type="object",
     *                     @OA\Property(property="id", type="integer", example=10),
     *                     @OA\Property(property="imageble_id", type="integer", example=1),
     *                     @OA\Property(property="url", type="string", example="promotions/image.jpg")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Promoción no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Promoción no encontrada")
     *         )
     *     )
     * )
     */

    public function getPromotion(int $promotionId): JsonResponse
    {
        $promotion = Promotion::select(
            'id',
            'title',
            'description'
        )
            ->with([
                'image:id,imageble_id,url'
            ])
            ->find($promotionId);
        if (!$promotion) {
            throw new NotFoundPromotion;
        }
        return new JsonResponse([
            'data' => $promotion
        ]);
    }
}
