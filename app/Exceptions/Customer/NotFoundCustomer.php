<?php

namespace App\Exceptions\Customer;

use Exception;

class NotFoundCustomer extends Exception
{
    public function __construct()
    {
        parent::__construct('Cliente no encontado', 404);
    }
}
