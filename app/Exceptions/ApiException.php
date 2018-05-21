<?php

namespace App\Exceptions;

class ApiException extends \Exception
{
    public function __construct($msg = '内部错误', $code = 500)
    {
        parent::__construct($msg, $code);
    }
}