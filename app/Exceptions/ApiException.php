<?php
/**
 * Created by PhpStorm.
 * User: Zhaoz
 * Date: 2018/5/27
 * Time: 10:49
 */

namespace App\Exceptions;


class ApiException extends \Exception
{
    function __construct($msg = '', $code = 500)
    {
        parent::__construct($msg, $code);
    }


}