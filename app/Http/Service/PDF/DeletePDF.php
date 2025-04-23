<?php

declare(strict_types=1);

namespace App\Http\Service\PDF;

use Illuminate\Support\Facades\Storage;

trait DeletePDF
{
    public function deletePDF(?string $path): bool
    {
        if(empty($path)){
            return false;
        }
        $relativePath = str_replace(asset('storage/') . '/', '', $path);
        if (Storage::disk('public')->exists($relativePath)) {
            return Storage::disk('public')->delete($relativePath);
        }
        return false;
    }
}