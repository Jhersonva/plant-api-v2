<?php

declare(strict_types=1);

namespace App\Http\Service\PDF;

use App\Models\Pdf;

trait StorePDF
{
    use SavePDF;
    public function storePDF(?string $pdfBase64): int
    {
        $path = $this->savePDFBase64($pdfBase64);
        $idPdf = Pdf::create([
                'url' => $path,
                'datetime' => now()
                ])->id;

        return $idPdf;
    }
}