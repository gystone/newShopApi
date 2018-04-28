<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api_admin', ['except' => ['login']]);
    }

    /**
     * 登录
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request()->only('username', 'password');

        if (! $token = auth('api_admin')->attempt($credentials)) {
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
        auth('api_admin')->logout();

        return respond(null, 204);
    }
}
