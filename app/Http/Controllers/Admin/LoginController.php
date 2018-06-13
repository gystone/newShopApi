<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\UserInfo;
use App\Models\Admin\AdminUser;

class LoginController extends BaseController
{
    /**
     * 登录
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request()->only('username', 'password');

        if (! $token = auth('api_admin')->attempt($credentials)) {
            return $this->failed('登录失败，用户名或密码错误', 401);
        }

        return $this->setStatusCode(201)
        ->success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api_admin')->factory()->getTTL() * 60
        ]);
    }

    /**
     * 登出
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api_admin')->logout();

        return $this->setStatusCode(204)->message('注销成功');
    }

    public function refresh()
    {
        $token = auth('api_admin')->refresh();

        return $this->setStatusCode(201)
        ->success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api_admin')->factory()->getTTL() * 60
        ]);
    }

    public function info()
    {
        $user = AdminUser::find(auth('api_admin')->user()->id);

        return $this->success(new UserInfo($user));
    }
}
