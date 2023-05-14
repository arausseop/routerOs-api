<?php

namespace App\Model\Exception\System;

use Exception;

class SystemNoParameterReceived extends Exception
{
    public static function throwException(string $parameter)
    {
        throw new self(sprintf('%s not received', $parameter));
    }
}
