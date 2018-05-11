<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * 登录
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request()->only('email', 'password');

        if (! $token = auth('api')->attempt($credentials)) {
            return respond('登录失败，用户名或密码错误', 401);
        }

        return respond_token($token);
    }

    /**
     * 登出
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return respond('注销成功', 204);
    }
}
