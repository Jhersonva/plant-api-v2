<?php

declare(strict_types=1);

namespace App\Http\Service\PDF;

use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isEmpty;

trait SavePDF
{
    public function savePDFBase64(?string $pdfBase64, string $folder = 'pdf'): ?string
{
    if (empty($pdfBase64)) {
        return null;
    }

    // Verificar si el tipo MIME es correcto para PDF
    if (!preg_match('/^data:application\/pdf;base64,/', $pdfBase64)) {
        throw new Exception("Formato de PDF no válido.");
    }

    // Decodificar el PDF
    $pdf = base64_decode(explode(',', $pdfBase64)[1]);
    $filename = Str::uuid() . '.pdf';
    $path = $folder . '/' . $filename;

    // Guardar el PDF en el almacenamiento público
    Storage::disk('public')->put($path, $pdf);

    return url('storage/' . $path);
}

}