<?php

namespace App\Model\Exception\Router;

use Exception;

class RouterNotFound extends Exception
{
    public static function throwException()
    {
        throw new self('routerOs not found');
    }
}
