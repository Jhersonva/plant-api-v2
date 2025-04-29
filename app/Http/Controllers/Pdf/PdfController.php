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
    
    /*public function pdfUpdateProduct(string $nameProduct, Request $request): JsonResponse
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
    }*/
}
