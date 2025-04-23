<?php

namespace App\Exceptions\Product;

use Exception;

class NotFoundProduct extends Exception
{
    public function __construct()
    {
        parent::__construct('Producto no encontrado', 404);
    }
}
