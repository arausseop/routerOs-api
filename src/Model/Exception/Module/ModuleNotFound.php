<?php

namespace App\Model\Exception\Module;

use Exception;

class ModuleNotFound extends Exception
{
    public static function throwException()
    {
        throw new self('module not found');
    }
}
