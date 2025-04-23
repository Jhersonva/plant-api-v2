<?php

namespace App\Exceptions\Auth;

use Exception;

class CredentialInvalid extends Exception
{
    public function __construct()
    {   
        parent::__construct('Credemciales invalidas', 401);
    }
}
