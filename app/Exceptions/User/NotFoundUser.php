<?php

namespace App\Exceptions\User;

use Exception;

class NotFoundUser extends Exception
{
    public function __construct()
    {   
        parent::__construct('Usuario no encontrado', 404);
    }
}
