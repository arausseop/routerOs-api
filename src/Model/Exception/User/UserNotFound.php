<?php

namespace App\Model\Exception\User;

use Exception;

class UserNotFound extends Exception
{
    public static function throwException()
    {
        throw new self('user not found');
    }
}
