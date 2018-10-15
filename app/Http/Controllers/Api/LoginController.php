<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\Wechat\WechatUser;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Log;
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
     * 测试登录
     *
     * @param \Illuminate\Http\Request $request
     * @throws
     * @return \Illuminate\Http\JsonResponse
     */
    public function testLogin(Request $request)
    {
        $openid = $request->openid;

        $user = WechatUser::where('openid', $openid)->first();

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
        $code = $request->code;

        if (empty($code)) {Log::info($code);
            return $this->failed('非法访问');
        }

        try {
            $client = new Client();
            $res_access_token = $client->request('GET', 'https://api.weixin.qq.com/sns/oauth2/access_token',
                [
                    'query' => [
                        'appid' => config('wechat.official_account.default.app_id'),
                        'secret' => config('wechat.official_account.default.secret'),
                        'code' => $code,
                        'grant_type' => 'authorization_code',
                    ]
                ]);
            $res_access_token_data = json_decode($res_access_token->getBody(), true);

            if (! isset($res_access_token_data['access_token'])) {Log::info($res_access_token_data);
                return $this->failed('获取access_token时发生错误：'.$res_access_token_data['errmsg']);
            }

            $access_token = $res_access_token_data['access_token'];
            $openid = $res_access_token_data['openid'];
            $res_user_info = $client->request('GET', 'https://api.weixin.qq.com/sns/userinfo',
                [
                    'query' => [
                        'access_token' => $access_token,
                        'openid' => $openid,
                        'lang' => 'zh_CN',
                    ]
                ]);
            $res_user_info_data = json_decode($res_user_info->getBody(), true);

            if (! isset($res_user_info_data['openid'])) {Log::info($res_user_info_data);
                return $this->failed('获取用户信息时发生错误：'.$res_user_info_data['errmsg']);
            }

            $user_info = array(
                'openid' => $res_user_info_data['openid'],
                'nickname' => $res_user_info_data['nickname'],
                'sex' => $res_user_info_data['sex'],
                'province' => $res_user_info_data['province'],
                'city' => $res_user_info_data['city'],
                'country' => $res_user_info_data['country'],
                'headimgurl' => $res_user_info_data['headimgurl'],
                'unionid' => $res_user_info_data['unionid'] ?? null,
                'status' => 'unsubscribe',
            );
        } catch (\Exception $exception) {Log::info($exception->getMessage());
            return $this->failed('获取用户信息时发生错误：'.$exception->getMessage());
        }

        $user = WechatUser::where('openid', $openid)->first();

        if (! $user) {
            $user = WechatUser::create($user_info);
        }

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function oldLogin()
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
