<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
/**
 * @OA\Schema(
 *     schema="Service",
 *     type="object",
 *     title="Service",
 *     properties={
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="title", type="string", example="Corte de Cabello"),
 *         @OA\Property(property="description", type="string", example="Corte de cabello personalizado"),
 *         @OA\Property(property="features", type="array", @OA\Items(type="string")),
 *         @OA\Property(property="image", type="object", nullable=true, 
 *             @OA\Property(property="url", type="string", example="https://example.com/image.jpg")
 *         )
 *     }
 * )
 */
class Service extends Model
{
    protected $fillable = ['title', 'description','features'];

    protected $hidden = ['created_at', 'updated_at'];

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageble');
    }

    protected static function booted()
    {
        static::deleting(function (Service $service) {
            $service->image()->delete();
        });
    }

}
