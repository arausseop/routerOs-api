<?php

namespace App\Model\Exception\RoleGroup;

use Exception;

class RoleGroupNotFound extends Exception
{
    public static function throwException()
    {
        throw new self('roleGroup not found');
    }
}
