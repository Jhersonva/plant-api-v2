<?php

namespace App\Exceptions\Auth;

use Exception;

class ExpiredCode extends Exception
{
    public function __construct()
    {   
        parent::__construct('Codigo expirado', 498);
    }
}
