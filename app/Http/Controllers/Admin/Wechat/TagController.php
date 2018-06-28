<?php

namespace App\Http\Controllers\Admin\Wechat;

use App\Http\Controllers\Admin\BaseController;
use App\Http\Requests\Wechat\TagRequest;
use App\Http\Resources\Wechat\UserCollection;
use App\Models\Wechat\WechatTag;
use App\Models\Wechat\WechatUser;
use EasyWeChat\OfficialAccount\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TagController extends BaseController
{
    private $tag;

    public function __construct(Application $app)
    {
        parent::__construct();
        $this->tag = $app->user_tag;
    }

    /**
     * 同步标签
     * @return mixed
     */
    public function sync()
    {
        DB::beginTransaction();

        try {
            $list = $this->tag->list();

            Log::info('正在同步标签');
            foreach ($list['tags'] as $k => $v) {
                $tag_users_list = $this->tag->usersOfTag($v['id']);

                $tag_users_db = [];
                if (isset($tag_users_list['data']['openid'])) {
                    foreach ($tag_users_list['data']['openid'] as $item) {
                        $tag_users_db[] = ['tag_id' => $v['id'], 'openid' => $item];
                    }
                }
                DB::table('wechat_tag_users')->insert($tag_users_db);

                WechatTag::updateOrCreate([
                    'id' => $v['id']
                ], [
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'count' => $v['count']
                ]);
            }
            Log::info('标签同步完成');

            DB::commit();

            return $this->message('同步完成');
        } catch (\Exception $exception) {Log::info($exception->getMessage());
            DB::rollBack();
            return $this->failed('同步失败，请稍候重试. 错误信息：'.$exception->getMessage());
        }
    }

    /**
     * 标签列表
     * @return mixed
     */
    public function list()
    {
        return $this->success(\request('page') ? WechatTag::paginate(\request('limit' ?? 20)) : WechatTag::all());
    }

    /**
     * 创建标签
     * name 标签名
     * @param Request $request
     * @return mixed
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function create(TagRequest $request)
    {
        $name = $request->name;

        $res = $this->tag->create($name);

        if (isset($res['tag'])) {
            WechatTag::create([
                'id' => $res['tag']['id'],
                'name' => $res['tag']['name'],
                'count' => 0
            ]);
            return $this->success($res['tag']);
        } else {
            return $this->failed('创建失败，请稍候重试');
        }
    }

    public function update(WechatTag $tag, TagRequest $request)
    {
        $name = $request->name;

        $res = $this->tag->update($tag->id, $name);

        if ($res['errcode'] === 0) {
            $tag->update(['name' => $name]);
            return $this->success($tag);
        } else {
            return $this->failed('编辑失败，请稍候重试');
        }
    }

    public function delete(WechatTag $tag)
    {
        $res = $this->tag->delete($tag->id);

        if ($res['errcode'] === 0) {
            $tag->delete();

            return $this->message('删除成功');
        } else {
            return $this->failed('删除失败，请稍候重试');
        }
    }

    public function tagUsers(TagRequest $request)
    {
        $openids = $request->openids;
        $tagids = $request->tagids;

        if (is_array($openids) && is_array($tagids)) {
            $untagids = WechatTag::whereNotIn('id', $tagids)->get(['id']);

            if (count($untagids)) {
                foreach ($untagids as $item) {
                    $untagid = $item->id;
                    $untag_user_db = DB::table('wechat_tag_users')->where('tag_id', $untagid)->whereIn('openid', $openids);
                    $res_untag = $this->tag->untagUsers($openids, $untagid);

                    if ($res_untag['errcode'] === 0) {
                        $untag_user_db->delete();
                        WechatTag::where('id', $untagid)->decrement('count', $untag_user_db->count());
                    }
                }
            }

            foreach ($tagids as $tagid) {
                $res = $this->tag->tagUsers($openids, $tagid);

                if ($res['errcode'] === 0) {
                    $tag_users_db = [];
                    foreach ($openids as $openid) {
                        $tag_users_db[] = ['tag_id' => $tagid, 'openid' => $openid];
                    }
                    DB::table('wechat_tag_users')->insert($tag_users_db);
                    WechatTag::where('id', $tagid)->increment('count', count($tag_users_db));
                }
            }

            return $this->message('标签设置成功');
        } else {
            return $this->failed('参数有误');
        }
    }

    public function userList(WechatTag $tag)
    {
        $tag_id = $tag->id;
        $users = WechatUser::whereExists(function ($query) use ($tag_id) {
            $query->select(DB::raw('openid'))
                ->from('wechat_tag_users')->where('tag_id', $tag_id)
                ->whereRaw(config('database.connections.mysql.prefix').'wechat_tag_users.openid = '.config('database.connections.mysql.prefix').'wechat_users.openid');
        });
        return $this->success(\request('page') ?
            new UserCollection($users->paginate(\request('limit') ?? 20)) :
            User::collection($users->get())
        );
    }
}