<?php

namespace App\Model\Exception\Router;

use Exception;

class RouterConnectionError extends Exception
{
    public static function throwException($exception)
    {
        throw new self("error: " . $exception['error']);
    }
}
