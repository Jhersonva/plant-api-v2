<?php

namespace App\Exceptions\Promotion;

use Exception;

class NotFoundPromotion extends Exception
{
    public function __construct()
    {
        parent::__construct('Promocion no encontrada', 404);
    }
}
