<?php

declare(strict_types=1);

namespace App\Http\Service\Image;

use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait SaveImage
{
    public function saveImage(?string $inputImage, string $folder = 'products'): ?string
    {
        if (empty($inputImage)) {
            return null;
        }
        if (filter_var($inputImage, FILTER_VALIDATE_URL)) {
            return $this->saveImageUrl($inputImage, $folder);
        }
        if (preg_match('/^data:image\/(\w+);base64,/', $inputImage)) {
            return $this->saveImageBase64($inputImage, $folder);
        }
        throw new Exception('Formato de imagen no reconocido: debe ser una URL o base64');
    }

    public function saveImageUrl(string $urlImage, string $folder = 'products'): ?string
    {
        $contents = file_get_contents($urlImage);

        if (!$contents) {
            throw new Exception('No se pudo descargar la imagen desde la URL.');
        }

        $extension = pathinfo(parse_url($urlImage, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $extension = strtolower($extension);

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception("Extensión no permitida: .$extension");
        }

        $filename = Str::uuid() . '.' . $extension;
        $path = $folder . '/' . $filename;

        Storage::disk('public')->put($path, $contents);

        return url('storage/' . $path);
    }
    
    public function saveImageBase64(?string $base64Image, string $folder = 'products'): ?string
    {
        if(empty($base64Image)){
            return null;
        }
        $image = base64_decode(explode(',',$base64Image)[1]);
        $fileExtension = $this->getFileExtension($base64Image);
        $filename = Str::uuid() . $fileExtension;
        $path = $folder . '/' . $filename;
        Storage::disk('public')->put($path, $image);
        return url('storage/'.$path);
    }

    public function getFileExtension(string $base64Image): string
    {
        $matches = [];
        if(preg_match('/data:image\/(?<type>.+);base64,/',$base64Image, $matches)){
            $mineType = $matches['type'];
            switch($mineType){
                case 'jpg':
                    return '.jpg';
                case 'jpeg':
                    return '.jpeg';
                case 'png':
                    return '.png';
                case 'webp':
                    return '.webp';
                case 'gif':
                    return '.gif';
                default:
                    throw new Exception('Not extension');
            }
        }
        throw new Exception('Invalid format');
    }
}