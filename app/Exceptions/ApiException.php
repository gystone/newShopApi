<?php
/**
 * Created by PhpStorm.
 * User: Zhaoz
 * Date: 2018/5/27
 * Time: 10:49
 */

namespace App\Exceptions;


use App\Traits\ApiResponse;

class ApiException extends \Exception
{
    use ApiResponse;

    function __construct(string $message = '', int $code = 500)
    {
        parent::__construct($message, $code);
    }

    public function render()
    {

        return $this->failed($this->message,$this->code);

    }


}