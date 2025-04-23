<?php

namespace App\Exceptions\Category;

use Exception;

class NotFoundCategory extends Exception
{
    public function __construct()
    {
        parent::__construct('Categoria no encontrada', 404);
    }
}
