<?php

declare(strict_types=1);

namespace App\Http\Service\PDF;

use App\Models\Product;

trait UpdatePDF
{
    use SavePDF, DeletePDF;

    public function updatePDF(Product $product, ?string $pdfBase64)
    {
        if (empty($pdfBase64)) return;

        // Guardar el nuevo PDF
        $newPdfUrl = $this->savePDFBase64($pdfBase64);

        $pdf = $product->pdf;

        if ($pdf) {
            // Eliminar el anterior del disco
            $this->deletePDF($pdf->url);

            // Actualizar datos del modelo
            $pdf->url = $newPdfUrl;
            $pdf->datetime = now();
            $pdf->save(); // ⚠️ Usa save()
        } else {
            // Crear nuevo PDF y asociarlo al producto
            $pdf = \App\Models\Pdf::create([
                'url' => $newPdfUrl,
                'datetime' => now(),
            ]);

            $product->pdf_id = $pdf->id;
            $product->save();
        }
    }
}