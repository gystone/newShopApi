<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WechatUserController extends BaseController
{

    public function index()
    {
        $list = User::orderBy('id','desc')->get();

        return $this->success($list, []);
    }


}
