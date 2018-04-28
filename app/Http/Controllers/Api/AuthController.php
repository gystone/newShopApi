<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Config;

class AuthController extends Controller
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
        Config::set('jwt.user', 'App\Models\User');
        Config::set('auth.providers.users.model', User::class);
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

        return respond(null, 204);
    }
}
