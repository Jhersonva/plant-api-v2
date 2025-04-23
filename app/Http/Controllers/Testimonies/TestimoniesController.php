<?php

namespace App\Http\Controllers\Testimonies;

use App\Exceptions\Testimonie\NotFoundTestimonie;
use App\Http\Controllers\Controller;
use App\Http\Requests\Testimonies\ValidateTestimoniesRequest;
use App\Http\Service\Image\DeleteImage;
use App\Http\Service\Image\SaveImage;
use App\Models\Testimonie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestimoniesController extends Controller
{
    use ValidateTestimoniesRequest,
    SaveImage,
    DeleteImage;

    /**
 * @OA\Post(
 *     path="/api/testimonies",
 *     summary="Registrar un testimonio",
 *     tags={"Testimonies"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name_customer", "description", "date", "qualification"},
 *             @OA\Property(property="name_customer", type="string", example="Juan Pérez"),
 *             @OA\Property(property="description", type="string", example="Excelente servicio y atención."),
 *             @OA\Property(property="date", type="string", format="date", example="2024-03-20"),
 *             @OA\Property(property="qualification", type="integer", example=5),
 *             @OA\Property(property="image", type="string", format="base64", example="data:image/png;base64,iVBORw0KGgoAAAANS..."),
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Testimonio registrado correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Testimonio registrado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Error de validación"),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 example={"name_customer": {"El nombre del cliente es obligatorio"}}
 *             )
 *         )
 *     )
 * )
 */
    public function storeTestimonies(Request $request): JsonResponse
    {
        $this->validateTestimoniesRequest($request);
        $testimonie = Testimonie::create([
            'name_customer' => $request->name_customer,
            'description' => $request->description,
            'date' => $request->date,
            'qualification' => $request->qualification
        ]);
        $image = $this->saveImage($request->image);
        $testimonie->image()->create([
            'url' => $image
        ]);

        return new JsonResponse(['data' => 'Testimonio registrado']);
    }

    /**
 * @OA\Put(
 *     path="/api/testimonies/{testimonieId}",
 *     summary="Actualizar un testimonio",
 *     tags={"Testimonies"},
 *     @OA\Parameter(
 *         name="testimonieId",
 *         in="path",
 *         required=true,
 *         description="ID del testimonio a actualizar",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name_customer", "description", "date", "qualification"},
 *             @OA\Property(property="name_customer", type="string", example="Juan Pérez"),
 *             @OA\Property(property="description", type="string", example="Servicio mejorado y excelente atención."),
 *             @OA\Property(property="date", type="string", format="date", example="2024-03-20"),
 *             @OA\Property(property="qualification", type="integer", example=4),
 *             @OA\Property(property="image", type="string", format="base64", example="data:image/png;base64,iVBORw0KGgoAAAANS..."),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Testimonio actualizado correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Testimonio actualizado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Testimonio no encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Testimonio no encontrado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Error de validación"),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 example={"name_customer": {"El nombre del cliente es obligatorio"}}
 *             )
 *         )
 *     )
 * )
 */
    public function updateTestimonies(int $testimonieId, Request $request): JsonResponse
    {
        $testimonie = Testimonie::find($testimonieId);
        if(!$testimonie){
            throw new NotFoundTestimonie;
        }
        $testimonie->update([
            'name_customer' => $request->name_customer,
            'description' => $request->description,
            'date' => $request->date,
            'qualification' => $request->qualification
        ]);
        $image = $this->saveImage($request->image);
        $this->deleteImage($testimonie->image->url);
        $testimonie->image()->update([
            'url' => $image
        ]);
        return new JsonResponse(['data' => 'Testimonio actulizado']);
    }

    /**
 * @OA\Delete(
 *     path="/api/testimonies/{testimonieId}",
 *     summary="Eliminar un testimonio",
 *     tags={"Testimonies"},
 *     @OA\Parameter(
 *         name="testimonieId",
 *         in="path",
 *         required=true,
 *         description="ID del testimonio a eliminar",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Testimonio eliminado correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Testimonio eliminado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Testimonio no encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Testimonio no encontrado")
 *         )
 *     )
 * )
 */
    public function deleteTestimonie(int $testimonieId): JsonResponse
    {   
        $testimonie = Testimonie::find($testimonieId);
        if(!$testimonie){
            throw new NotFoundTestimonie;
        }
        $testimonie->delete();
        $this->deleteImage($testimonie->image->url);
        return new JsonResponse(['data' => 'Testiminio eliminado']);
    }

/**
 * @OA\Get(
 *     path="/testimonies",
 *     summary="Obtiene todos los testimonios",
 *     description="Devuelve una lista paginada de testimonios con la posibilidad de filtrar por cliente.",
 *     tags={"Testimonies"},
 *     @OA\Parameter(
 *         name="customer",
 *         in="query",
 *         required=false,
 *         description="Filtrar testimonios por nombre del cliente",
 *         @OA\Schema(type="string", example="Juan Perez")
 *     ),
 *     @OA\Parameter(
 *         name="limit",
 *         in="query",
 *         required=false,
 *         description="Cantidad de testimonios por página",
 *         @OA\Schema(type="integer", example=10)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Lista paginada de testimonios",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name_customer", type="string", example="Juan Perez"),
 *                     @OA\Property(property="description", type="string", example="Excelente servicio"),
 *                     @OA\Property(property="qualification", type="integer", example=5),
 *                     @OA\Property(property="date", type="string", format="date", example="2024-03-24"),
 *                     @OA\Property(property="image", type="object",
 *                         @OA\Property(property="url", type="string", example="https://example.com/image.jpg"),
 *                     )
 *                 ),
 *                     
 *             ),
 *             @OA\Property(property="current_page", type="integer", example=1),
 *             @OA\Property(property="total", type="integer", example=50),
 *             @OA\Property(property="last_page", type="integer", example=5),
 *             @OA\Property(property="next_page", type="string", nullable=true, example="http://api.com/testimonies?page=2"),
 *             @OA\Property(property="prev_page", type="string", nullable=true, example=null)
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Solicitud incorrecta"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno del servidor"
 *     )
 * )
 */
    public function getAllTestimonies(Request $request): JsonResponse
    {
        $nameClient = $request->query('customer');
        $limit = $request->query('limit');
        $testimonies = Testimonie::select(
            'id', 
            'name_customer', 
            'description', 
            'qualification',
            'date')
        ->with(['image:id,imageble_id,url'])
        ->where('name_customer', 'like', "%{$nameClient}%")
        ->paginate($limit);
        return new JsonResponse([
            'data' => $testimonies->items(),
            'current_page' => $testimonies->currentPage(),
            'total' => $testimonies->total(),
            'last_page' => $testimonies->lastPage(),
            'next_page' => $testimonies->nextPageUrl(),
            'prev_page' => $testimonies->previousPageUrl()
        ]);
    }

/**
 * @OA\Get(
 *     path="/api/testimonies/{id}",
 *     summary="Obtener un testimonio por ID",
 *     description="Devuelve un testimonio específico junto con su imagen relacionada.",
 *     tags={"Testimonies"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID del testimonio a consultar",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Testimonio encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name_customer", type="string", example="Juan Pérez"),
 *                 @OA\Property(property="description", type="string", example="Muy buen servicio."),
 *                 @OA\Property(property="qualification", type="integer", example=5),
 *                 @OA\Property(property="date", type="string", format="date", example="2024-04-09"),
 *                 @OA\Property(property="image", type="object",
 *                     @OA\Property(property="url", type="string", example="https://example.com/storage/testimonies/image.jpg")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Testimonio no encontrado"
 *     )
 * )
 */
    public function getTestimonie(int $testimonieId): JsonResponse
    {   
        $testimonie = Testimonie::select(
            'id', 
            'name_customer', 
            'description', 
            'qualification', 
            'date')
            ->with(['image:id,imageble_id,url'])
            ->find($testimonieId);
        if(!$testimonie){
            throw new NotFoundTestimonie;
        }
        return new JsonResponse(['data' => $testimonie]);
    }
}
