<?php

namespace App\Http\Controllers\AboutUsHome;

use App\Http\Controllers\Controller; // Agrega esta línea
use App\Http\Requests\AboutUsHome\ValidateAboutUsHome;
use App\Http\Service\Image\DeleteImage;
use App\Http\Service\Image\SaveImage;
use App\Models\AboutUsHome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="AboutUsHome",
 *     description="Endpoints para gestionar la sección About Us en la página de inicio"
 * )
 */
class AboutUsHomeController extends Controller
{
    use SaveImage;
    use DeleteImage;
    use ValidateAboutUsHome;

    /**
     * @OA\Get(
     *     path="/api/about-us-home",
     *     summary="Obtener datos de About Us Home",
     *     tags={"AboutUsHome"},
     *     @OA\Response(
     *         response=200,
     *         description="Datos obtenidos correctamente",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function getAboutUsHome() 
    {
        $aboutUsHome = AboutUsHome::with('images')->first();

        if ($aboutUsHome->images) {
            $aboutUsHome->images->each(function ($image) {
                $image->url = asset('storage/' . basename($image->url));
            });
        }

        return response()->json($aboutUsHome, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/about-us-home/{idAboutUsHome}",
     *     summary="Actualizar los datos de About Us Home",
     *     tags={"AboutUsHome"},
     *     @OA\Parameter(
     *         name="idAboutUsHome",
     *         in="path",
     *         required=true,
     *         description="ID de About Us Home",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"text_section_one", "text_section_two"},
     *             @OA\Property(property="text_section_one", type="string", example="Nuestra misión es..."),
     *             @OA\Property(property="text_section_two", type="string", example="Nuestro compromiso es...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Información actualizada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Información actualizada correctamente"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="No se enviaron datos válidos para actualizar"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function updateAboutUsHome(Request $request)
    {
        // Llamamos al método de validación del trait
        $this->validateAboutUsHome($request);

        $aboutUsHome = AboutUsHome::firstOrCreate([]);
        $data = array_filter($request->only(['text_section_one', 'text_section_two']), function ($value) {
            return $value !== null && $value !== '';
        });

        if (!empty($data)) {
            $aboutUsHome->update($data);
        }

        return response()->json([
            'message' => 'Información actualizada correctamente',
            'data' => $aboutUsHome
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/about-us-home/image",
     *     summary="Actualizar la imagen de About Us Home",
     *     tags={"AboutUsHome"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"image"},
     *             @OA\Property(property="image", type="string", format="base64", example="data:image/png;base64,iVBORw0KGgoAAAANS...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Imagen actualizada con éxito",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al actualizar la imagen"
     *     )
     * )
     */
    public function updateImageToAboutUsHome(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|string',
            ]);

            $aboutUsHome = AboutUsHome::firstOrCreate([]);

            DB::transaction(function () use ($aboutUsHome, $request) {
                // Obtener la imagen actual
                $existingImage = $aboutUsHome->images()->latest()->first();

                // Guardar la nueva imagen
                $imagePath = $this->saveImage($request->image, 'about_us_home_images');

                if (!$imagePath) {
                    throw new \Exception("Error al guardar la imagen.");
                }

                if ($existingImage) {
                    // Eliminar la imagen anterior del almacenamiento
                    $this->deleteImage($existingImage->url);

                    // Actualizar la URL en la base de datos en lugar de eliminar el registro
                    $existingImage->update(['url' => $imagePath]);
                } else {
                    // Si no hay imagen previa, crear un nuevo registro
                    $aboutUsHome->images()->create(['url' => $imagePath]);
                }
            });

            return response()->json([
                'message' => 'Imagen actualizada con éxito',
                'path' => $aboutUsHome->images()->latest()->first()->url,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar la imagen: ' . $e->getMessage(),
            ], 500);
        }
    }
}
