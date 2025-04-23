<?php

declare(strict_types=1);

namespace App\Http\Service\PDF;

use App\Models\Product;

trait UpdatePDF
{
    use SavePDF;
    public function updatePDF(Product $product, ?string $pdfBase64){
        $pdf = $this->savePDFBase64($pdfBase64);
        $product->pdf()->update([
            'url' => $pdf
        ]);
    }
}