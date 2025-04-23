<?php 


namespace App\Http\Controllers\InfoContact;

use App\Http\Controllers\Controller; // Agrega esta línea
use App\Models\InfoContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="InfoContact",
 *     description="Endpoints para gestionar la información de contacto"
 * )
 */
class InfoContactController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/info-contact",
     *     summary="Obtener la información de contacto",
     *     tags={"InfoContact"},
     *     @OA\Response(
     *         response=200,
     *         description="Información de contacto obtenida exitosamente",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Información no encontrada"
     *     )
     * )
     */
    public function getInfoContact()
    {
        $infoContact = InfoContact::first();

        return $infoContact
            ? response()->json($infoContact, 200)
            : response()->json(['message' => 'Información no encontrada'], 404);
    }

    /**
     * @OA\Put(
     *     path="/api/info-contact/{id}",
     *     summary="Actualizar la información de contacto",
     *     tags={"InfoContact"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la información de contacto a actualizar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="location", type="string", maxLength=100, example="Lima, Perú"),
     *             @OA\Property(property="cellphone", type="string", minLength=9, maxLength=9, example="987654321"),
     *             @OA\Property(property="email", type="string", format="email", maxLength=320, example="contacto@empresa.com"),
     *             @OA\Property(property="attention_hours", type="string", maxLength=100, example="Lunes a Viernes de 9am a 6pm")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Información de contacto actualizada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Información de contacto actualizada correctamente"),
     *             @OA\Property(property="data", type="object", 
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="location", type="string", example="Lima, Perú"),
     *                 @OA\Property(property="cellphone", type="string", example="987654321"),
     *                 @OA\Property(property="email", type="string", format="email", example="contacto@empresa.com"),
     *                 @OA\Property(property="attention_hours", type="string", example="Lunes a Viernes de 9am a 6pm")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="No se enviaron datos válidos para actualizar",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No se enviaron datos válidos para actualizar.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Información de contacto no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Información de contacto no encontrada.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object",
     *                 @OA\AdditionalProperties(type="array",
     *                     @OA\Items(type="string", example="El campo location es obligatorio.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function updateInfoContact(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'location' => 'nullable|string|max:100',
            'cellphone' => 'nullable|string|size:9',
            'email' => 'nullable|email|max:320',
            'attention_hours' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $infoContact = InfoContact::findOrFail($id);

        $data = array_filter($request->only(['location', 'cellphone', 'email', 'attention_hours']), function ($value) {
            return $value !== null && $value !== '';
        });

        if (empty($data)) {
            return response()->json(['message' => 'No se enviaron datos válidos para actualizar.'], 400);
        }

        $infoContact->update($data);

        return response()->json([
            'message' => 'Información de contacto actualizada correctamente',
            'data' => $infoContact
        ], 200);
    }
}
