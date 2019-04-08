<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\User;
use App\Models\Wechat\WechatUser;
use EasyWeChat\MiniProgram\Encryptor;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class LoginMiniController extends ApiController
{
    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        auth()->shouldUse('api');
    }

    /**
     * 测试登录
     *
     * @param \Illuminate\Http\Request $request
     * @throws
     * @return \Illuminate\Http\JsonResponse
     */
    public function testLogin(Request $request)
    {
        $openid = $request->openid;
        $user = User::where('openid', $openid)->firstOrFail();
        $token = auth('api')->fromUser($user);
        return $this->setStatusCode(201)
            ->success([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ]);
    }

    /**
     * 登录
     *
     * @param \Illuminate\Http\Request $request
     * @throws
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $app = app('wechat.mini_program');
        $code = $request->code;
        $encryptedData = $request->encryptedData;
        $iv = $request->iv;

        if (empty($code) || empty($encryptedData) || empty($iv)) {
            return $this->failed('非法访问');
        }

        try {
            $res_session = $app->auth->session($code);

            $pc = new Encryptor(env('WECHAT_MINI_PROGRAM_APPID'));
            $errcode = $pc->decryptData($res_session['session_key'], $iv, $encryptedData);

            if (!empty($errcode)) {
                $user_info = [
                    'openid' => $errcode['openId'],
                    'nickname' => $errcode['nickName'],
                    'headimgurl' => $errcode['avatarUrl'],
//                    'avatar' => $errcode['avatarUrl'],
                    'country' => $errcode['country'],
                    'province' => $errcode['province'],
                    'city' => $errcode['city'],
                ];
            } else {
                return $this->failed('获取用户信息时发生错误：' . $errcode['errmsg']);
            }
        } catch (\Exception $exception) {
            return $this->failed('获取用户信息时发生错误：' . $exception->getMessage());
        }

        $user = User::where('openid', $res_session['openid'])->first();

        if (!$user) {
            $user = User::create($user_info);
        }

        $token = auth('api')->fromUser($user);

        $success_data = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ];

        return $this->success($success_data, 200);
    }

    /**
     * 登录
     * @return \Illuminate\Http\JsonResponse
     */
    public function oldLogin()
    {
        $credentials = request()->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
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


    //获取小程序码
    public function getMiniCode(Request $request)
    {
        $scene = $request->scene ?? '';
        $page = $request->page ?? '';

        $app = app('wechat.mini_program');
        $app_code = $app->app_code;

        $res = $app_code->getUnlimit($scene, ['page' => $page]);

        return $res;
    }
}
