<?php

namespace App\Exceptions\Servics;

use Exception;

class NotFoundService extends Exception
{
    public function __construct()
    {
        parent::__construct('Servicio no encontrado', 404);
    }

    public static function serviceLoadError()
    {
        return new self('Error al cargar los servicios');
    }
    
}