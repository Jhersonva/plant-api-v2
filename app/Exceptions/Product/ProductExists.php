<?php

namespace App\Exceptions\Product;

use Exception;

class ProductExists extends Exception
{
    public function __construct()
    {
        parent::__construct('El producto ya existe', 409);
    }
}
