<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;

class LoginController extends ApiController
{
    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        auth()->shouldUse('api');
    }

    /**
     * 登录
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request()->only('email', 'password');

        if (! $token = auth('api')->attempt($credentials)) {
            return $this->failed('登录失败，用户名或密码错误', 401);
        }

        return $this->setStatusCode(201)
            ->success([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ]);
    }

    /**
     * 登出
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return $this->setStatusCode(204)->message('注销成功');
    }

    public function refresh()
    {
        $token = auth('api')->refresh();

        return $this->setStatusCode(201)
        ->success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
