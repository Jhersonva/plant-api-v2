<?php

namespace App\Http\Controllers\Policy;

use App\Http\Requests\Policy\ValidatePolicyUpdate;
use App\Http\Controllers\Controller;
use App\Http\Service\Image\DeleteImage;
use App\Http\Service\Image\SaveImage;
use App\Models\Policies;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    use ValidatePolicyUpdate, SaveImage, DeleteImage;

    /**
     * @OA\Get(
     *     path="/api/policies",
     *     summary="Obtener la política",
     *     description="Retorna la política con su imagen asociada.",
     *     tags={"Policies"},
     *     @OA\Response(
     *         response=200,
     *         description="Éxito: Retorna la política",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Política de privacidad"),
     *             @OA\Property(property="description", type="string", example="Esta es la política..."),
     *             @OA\Property(
     *                 property="image",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=10),
     *                 @OA\Property(property="url", type="string", example="https://example.com/image.jpg")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Política no encontrada"
     *     )
     * )
     */
    public function getPolicy() : JsonResponse
    {
        $policies = Policies::with('image')->first();
        return new JsonResponse($policies, 200);
    }

    /** 
     * @OA\Put(
     *     path="/api/policies/{idPolicies}",
     *     summary="Actualizar la política",
     *     security={{"bearerAuth": {}}},
     *     description="Actualiza la información de la política y su imagen.",
     *     tags={"Policies"},
     *     @OA\Parameter(
     *         name="idPolicies",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "description", "aboutValue"},
     *             @OA\Property(property="name", type="string", example="Política actualizada"),
     *             @OA\Property(property="description", type="string", example="Nueva descripción de la política."),
     *             @OA\Property(property="aboutValue", type="string", example="Información adicional sobre la política."),
     *             @OA\Property(property="image", type="string", format="byte", example="data:image/png;base64,iVBORw0KGgoAAAANS...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Política actualizada correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Policy updated successfully"),
     *             @OA\Property(
     *                 property="policy",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Política actualizada"),
     *                 @OA\Property(property="description", type="string", example="Nueva descripción..."),
     *                 @OA\Property(property="aboutValue", type="string", example="Información adicional actualizada."),
     *                 @OA\Property(
     *                     property="image",
     *                     type="object",
     *                     @OA\Property(property="url", type="string", example="https://example.com/image.jpg")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor al guardar la imagen",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error saving image"),
     *             @OA\Property(property="error", type="string", example="Error message details")
     *         )
     *     )
     * )
     */
    public function updatePolicy(Request $request, $idPolicies) : JsonResponse
    {
        $policy = Policies::findOrFail($idPolicies);
        $validateData = $this->validatePolicyUpdate($request);
        
        // Si hay imagen en la solicitud
        if ($request->has('image') && !empty($request->image)) {
            try {
                $existingImage = $policy->image()->latest()->first();
                $image = $this->saveImage($request->image, 'policies');
                
                if ($policy->image) {
                    // Si la imagen ya existe, actualizamos
                    $policy->image()->update(['url' => $image]);

                } else {
                    // Si no hay imagen, creamos una nueva relación
                    $policy->image()->create(['url' => $image]);
                }
                if ($existingImage) {
                    // Eliminar la imagen anterior del almacenamiento
                    $this->deleteImage($existingImage->url);
                }
                $policy->load('image');
            } catch (\Exception $e) {
                return new JsonResponse([
                    'message' => 'Error saving image',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
        
        $policy->update($validateData);
        $policy->save();

        return new JsonResponse([
            'message' => 'Policy updated successfully',
            'policy' => $policy
        ], 200);
    }
}
