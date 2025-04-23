<?php

namespace App\Exceptions\Testimonie;

use Exception;

class NotFoundTestimonie extends Exception
{
    public function __construct()
    {
        parent::__construct('Testimonio no encontrado', 404);
    }
}
