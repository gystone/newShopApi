<?php

namespace App\Exceptions;

class ApiException extends \Exception
{
    public function __construct($msg = '', $code = 500)
    {
        parent::__construct($msg, $code);
    }
}