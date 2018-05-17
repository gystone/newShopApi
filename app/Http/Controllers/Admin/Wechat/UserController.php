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
        try {
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
                        'headimgurl' => $user['headimgurl'],
                        'subscribe_time' => date('Y-m-d H:i:s', $user['subscribe_time']),
                        'status' => 'subscribe',
                        'unionid' => $user['unionid'] ?? '',
                        'remark' => $user['remark'],
                        'tagid_list' => $user['tagid_list'],
                        'subscribe_scene' => $user['subscribe_scene'],
                    ]
                );
                Log::info('同步粉丝入库' . $user['nickname'] . $v);
            }

            return $this->message('同步成功');
        } catch (\Exception $exception) {
            return $this->failed('同步失败，请稍候重试');
        }
    }

    public function list()
    {
        return $this->success(WechatUser::paginate(10));
    }

    public function remark(WechatUser $user, Request $request)
    {
        $remark = $request->remark;

        $res = $this->user->remark($user->openid, $remark);

        if ($res['errcode'] === 0) {
            $user->update(['remark' => $remark]);
            return $this->message('设置成功');
        } else {
            return $this->failed('设置失败，请稍候重试');
        }
    }

    public function block(Request $request)
    {
        $openids = $request->openid;

        if (is_array($openids)) {
            $res = $this->user->block($openids);
            if ($res['errcode'] === 0) {
                return $this->message('拉黑设置成功');
            } else {
                return $this->failed('拉黑失败，请稍候重试');
            }
        } else {
            return $this->failed('参数有误');
        }
    }

    public function unblock(Request $request)
    {
        $openids = $request->openid;

        if (is_array($openids)) {
            $res = $this->user->unblock($openids);
            if ($res['errcode'] === 0) {
                return $this->message('取消拉黑设置成功');
            } else {
                return $this->failed('取消拉黑失败，请稍候重试');
            }
        } else {
            return $this->failed('参数有误');
        }
    }
}
