<?php

namespace App\Http\Controllers;

use App\Exceptions\OpenidNotFoundException;
use Illuminate\Http\Request;

class ApiAuthController extends ApiController
{
    public $openid;
    /**
     * ApiController constructor.
     * @throws OpenidNotFoundException
     */
    public function __construct()
    {
        if(!is_null(auth('api')->user())){
            $this->openid = auth('api')->user()->openid;
            if($this->openid == null)throw new OpenidNotFoundException();
        }else{
            $this->openid = null;
            throw new OpenidNotFoundException();
        }
    }
}
