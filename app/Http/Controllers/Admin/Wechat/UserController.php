<?php

namespace App\Http\Controllers\Admin\Wechat;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Wechat\UserRequest;
use App\Http\Resources\Wechat\User;
use App\Http\Resources\Wechat\UserCollection;
use App\Models\Wechat\WechatUser;
use EasyWeChat\OfficialAccount\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        DB::beginTransaction();

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
                    ]
                );
                DB::table('wechat_tag_users')->where('openid', $v)->delete();
                $tag_users_db = [];
                foreach ($user['tagid_list'] as $k1 => $v1) {
                    $tag_users_db[] = ['tag_id' => $v1, 'openid' => $v];
                }
                DB::table('wechat_tag_users')->insert($tag_users_db);
            }

            DB::commit();

            return $this->message('同步成功');
        } catch (\Exception $exception) {Log::info($exception->getMessage());
            DB::rollBack();
            return $this->failed('同步失败，请稍候重试');
        }
    }

    public function list()
    {
        return $this->success(new UserCollection(WechatUser::where('status', 'subscribe')->orderBy('subscribe_time', 'desc')->paginate(10)));
    }

    public function remark(WechatUser $user, UserRequest $request)
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

    public function block(UserRequest $request)
    {
        $openids = $request->openids;

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

    public function unblock(UserRequest $request)
    {
        $openids = $request->openids;

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

    public function blacklist()
    {
        $list = $this->user->blacklist();

        $res_list = [];

        if (isset($list['data']) && count($list['data']['openid'])) {
            foreach ($list['data']['openid'] as $k => $v) {
                $res_list[] = new User(WechatUser::where('openid', $v)->first());
            }
        }
        return $this->success($res_list);
    }
}
