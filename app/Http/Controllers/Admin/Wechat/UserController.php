<?php

namespace App\Http\Controllers\Admin\Wechat;

use App\Http\Controllers\ApiController;
use App\Models\Wechat\WechatUser;
use EasyWeChat\OfficialAccount\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends ApiController
{
    private $user;

    public function __construct(Application $app)
    {
        auth()->shouldUse('api_admin');
        $this->user = $app->user;
    }

    public function sync()
    {
//        try {
            $list = $this->user->list();
            foreach ($list['data']['openid'] as $k => $v) {
                $user = $this->user->get($v);
                WechatUser::updateOrCreate(
                    [
                        'openid' => $v
                    ],
                    [
                        'openid' => $v,
                        'nickname' => $user['nickname'],
                        'sex' => $user['sex'],
                        'city' => $user['city'],
                        'province' => $user['province'],
                        'country' => $user['country'],
                        'headiimgurl' => $user['headimgurl'], // FIXME: 修改字段名多了个i
                        'subscribe_time' => date('Y-m-d H:i:s', $user['subscribe_time']),
                        'status' => 'subscribe'
                    ]
                );
                Log::info('同步粉丝入库' . $user['nickname'] . $v);dd($list);
            }

            return $this->message('同步成功');
//        } catch (\Exception $exception) {
//            return $this->failed('同步失败，请稍候重试');
//        }
    }

    public function list()
    {
        return $this->success(WechatUser::all());
    }
}
