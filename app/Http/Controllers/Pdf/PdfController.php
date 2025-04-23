<?php

namespace App\Http\Controllers\Pdf;

use App\Exceptions\Product\NotFoundProduct;
use App\Http\Controllers\Controller;
use App\Http\Service\PDF\DeletePDF;
use App\Http\Service\PDF\SavePDF;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    use SavePDF, DeletePDF;
    /**
 * @OA\Put(
 *     path="/api/products/{nameProduct}/pdf",
 *     summary="Actualizar el PDF de un producto",
 *     description="Elimina el PDF anterior de un producto y guarda uno nuevo.",
 *     tags={"Products"},
 *     @OA\Parameter(
 *         name="nameProduct",
 *         in="path",
 *         required=true,
 *         description="Nombre del producto cuyo PDF se actualizará.",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"pdf"},
 *             @OA\Property(
 *                 property="pdf",
 *                 type="string",
 *                 format="base64",
 *                 description="Archivo PDF en formato Base64."
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="PDF actualizado correctamente.",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="Pdf actualizado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Producto no encontrado.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Producto no encontrado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Error en la solicitud.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="El archivo PDF es inválido")
 *         )
 *     )
 * )
 */
    public function pdfUpdateProduct(string $nameProduct, Request $request): JsonResponse
    {
        $product = Product::where('name', $nameProduct)->first();
        if(!$product){
            throw new NotFoundProduct;
        }
        $this->deletePDF($product->pdf->url);
        $pdf = $this->savePDFBase64($request->pdf);
        $product->pdf()->update([
            'url' => $pdf,
            'dateTime' => now()
        ]);
        return new JsonResponse(['data' => 'Pdf actualizado']);
    }
}
