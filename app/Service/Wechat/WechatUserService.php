<?php

namespace App\Service\Wechat;

use App\Jobs\SaveWechatUsers;
use App\Models\Wechat\WechatUser;
use EasyWeChat\OfficialAccount\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WechatUserService
{
    public function sync()
    {
        $app = app('wechat.official_account');
        WechatUser::truncate();
        $nextOpenid = null;
        do {
            $users_db = [];
            $list = $app->user->list($nextOpenid);

            if (!empty($list['data']['openid'])) {
                $collection = collect($list['data']['openid']);
                $openidChunks = $collection->chunk(100);
                $openidChunksList = $openidChunks->toArray();
                foreach ($openidChunksList as $openidList) {
                    foreach ($openidList as $openid) {
                        $users_db[] = ['openid' => $openid];
                    }

                    // 插入数据库
                    WechatUser::insert($users_db);
                    $users_db = [];

                    // 同步粉丝基本信息
                    SaveWechatUsers::dispatch(json_encode($openidList));
                }
            }


            // 总条数大于1w,并且当前获取的条数大于等于1w时,否则退出循环
            if ($list['total'] > $list['count'] && $list['count'] == 10000) {
                $nextOpenid = $list['next_openid'];
            } else {
                break;
            }
        } while(true);
    }

    public function updateWechatUser($openidList)
    {
        $app = app('wechat.official_account');
        $res = $app->user->select(array_values($openidList));Log::info($openidList);Log::info($res);
        if (!empty($res['user_info_list'])) {
            foreach ($res['user_info_list'] as $user) {
                if ($user['subscribe'] == 1) {
                    WechatUser::where('openid', $user['openid'])->update([
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
                        'is_blacklist' => 0,
                    ]);
                } elseif ($user['subscribe'] == 0) {
                    WechatUser::where('openid', $user['openid'])->delete();
                } else {
                    Log::info($user);
                }
            }
        }
    }
}