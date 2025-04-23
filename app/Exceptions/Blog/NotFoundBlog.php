<?php

namespace App\Exceptions\Blog;

use Exception;

class NotFoundBlog extends Exception
{
    public function __construct()
    {
        parent::__construct('Blog no encontrado', 404);
    }
}
