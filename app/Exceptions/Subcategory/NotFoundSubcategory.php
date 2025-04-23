<?php

namespace App\Exceptions\Subcategory;

use Exception;

class NotFoundSubcategory extends Exception
{
    public function __construct()
    {
        parent::__construct('Subcategoria no encontrada', 404);
    }
}
