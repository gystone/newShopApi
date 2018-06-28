<?php

namespace App\Http\Controllers\Admin\Wechat;

use App\Http\Controllers\Admin\BaseController;
use App\Http\Requests\Wechat\UserRequest;
use App\Http\Resources\Wechat\User;
use App\Http\Resources\Wechat\UserCollection;
use App\Jobs\SyncWechatUsers;
use App\Models\Wechat\WechatTag;
use App\Models\Wechat\WechatUser;
use EasyWeChat\OfficialAccount\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends BaseController
{
    private $user;
    private $tag;

    public function __construct(Application $app)
    {
        parent::__construct();
        $this->user = $app->user;
        $this->tag = $app->user_tag;
    }

    public function sync()
    {
        DB::beginTransaction();

        try {

            $tag_list = $this->tag->list();

            Log::info('正在同步标签');
            DB::table('wechat_tag_users')->truncate();
            WechatTag::truncate();
            $tags_db = [];
            foreach ($tag_list['tags'] as $k => $v) {
                $tag_users_list = $this->tag->usersOfTag($v['id']);

                $tag_users_db = [];
                if (isset($tag_users_list['data']['openid'])) {
                    foreach ($tag_users_list['data']['openid'] as $item) {
                        $tag_users_db[] = ['tag_id' => $v['id'], 'openid' => $item];
                    }
                }

                $tags_db[] = [
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'count' => $v['count']
                ];
            }
            DB::table('wechat_tag_users')->insert($tag_users_db);
            WechatTag::insert($tags_db);
            Log::info('标签同步完成');

            // 同步粉丝
            SyncWechatUsers::dispatch();

            $beginOpenid = null;
            $blacklist_db = [];
            do {
                $blacklist = $this->user->blacklist($beginOpenid);
                if ($blacklist['count'] === 0) {
                    break;
                }

                $blacklist_db = array_merge($blacklist_db, $blacklist['data']['openid']);

                if ($blacklist['count'] < 10000) {
                    break;
                }
                $beginOpenid = $blacklist['next_openid'];
            } while(true);

            if (count($blacklist_db)) {
                WechatUser::whereIn('openid', $blacklist_db)->update(['is_blacklist' => 1]);
            }

            DB::commit();

            return $this->message('同步成功');
        } catch (\Exception $exception) {Log::info($exception->getMessage());
            DB::rollBack();
            return $this->failed('同步失败，请稍候重试. 错误信息：'.$exception->getMessage());
        }
    }

    public function syncUser()
    {
        $userList = WechatUser::whereNull('nickname')->whereNull('headimgurl')->get(['openid']);
        foreach ($userList as $user) {
            $res = $this->user->get($user->openid);
            if (isset($res['openid'])) {
                WechatUser::where('openid', $res['openid'])->update([
                    'nickname' => $res['nickname'],
                    'sex' => $res['sex'],
                    'city' => $res['city'],
                    'province' => $res['province'],
                    'country' => $res['country'],
                    'headimgurl' => $res['headimgurl'],
                    'subscribe_time' => date('Y-m-d H:i:s', $res['subscribe_time']),
                    'status' => 'subscribe',
                    'unionid' => $res['unionid'] ?? '',
                    'remark' => $res['remark'],
                    'is_blacklist' => 0,
                ]);
            } else {
                Log::info($res);
            }
        }
    }

    public function list()
    {
        $users = WechatUser::where('status', 'subscribe')->orderBy('subscribe_time', 'desc');

        return $this->success(\request('page') ?
            new UserCollection($users->paginate(\request('limit') ?? 20)) :
            User::collection($users->get())
        );
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
                WechatUser::whereIn('openid', $openids)->update(['is_blacklist' => 1]);
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
                WechatUser::whereIn('openid', $openids)->update(['is_blacklist' => 0]);
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
        $list = WechatUser::where('is_blacklist', 1);
        return $this->success(\request('page') ?
            new UserCollection($list->paginate(\request('limit') ?? 20)) :
            User::collection($list->get())
        );
    }

    public function search()
    {
        $content = trim(\request()->get('content'));

        if (! $content) {
            return $this->success([]);
        }

        $list = WechatUser::where(['status' => 'subscribe', ['nickname', 'like', '%'.$content.'%']])->orderBy('subscribe_time', 'desc')->paginate(20);

        return $this->success(new UserCollection($list));
    }
}
