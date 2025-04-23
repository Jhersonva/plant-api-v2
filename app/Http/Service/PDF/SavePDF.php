<?php

declare(strict_types=1);

namespace App\Http\Service\PDF;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isEmpty;

trait SavePDF
{
    public function savePDFBase64(?string $pdfBase64, string $folder = 'pdf'): ?string
    {
        if(empty($pdfBase64)){
            return null;
        }
        $pdf = base64_decode(explode(',', $pdfBase64)[1]);
        $filename = Str::uuid() . '.pdf';
        $path = $folder . '/' . $filename;
        Storage::disk('public')->put($path, $pdf);
        return url('storage/'.$path);
    }
}