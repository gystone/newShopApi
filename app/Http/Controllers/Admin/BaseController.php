<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;

class BaseController extends ApiController
{
    public function __construct()
    {
        auth()->shouldUse('api_admin');
    }
}
