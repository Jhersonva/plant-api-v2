<?php

namespace App\Exceptions\AboutUs;

use Exception;

class NotFoundAboutUs extends Exception
{
    public function render()
    {
        return response()->json(['error' => 'Información de About Us no encontrada'], 404);
    }
}
