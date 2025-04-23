<?php

namespace App\Exceptions\Servics;

use Exception;

class NotFoundFeature extends Exception
{
    public function __construct()
    {
        parent::__construct('Característica no encontrada', 404);
    }
}