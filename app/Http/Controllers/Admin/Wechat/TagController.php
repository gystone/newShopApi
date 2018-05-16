<?php

namespace App\Http\Controllers\Admin\Wechat;

use App\Http\Controllers\ApiController;
use App\Models\Wechat\WechatTag;
use EasyWeChat\OfficialAccount\Application;
use Illuminate\Support\Facades\Log;

class TagController extends ApiController
{
    private $tag;

    public function __construct(Application $app)
    {
        auth()->shouldUse('api_admin');
        $this->tag = $app->user_tag;
    }

    public function sync()
    {
        try {
            $list = $this->tag->list();

            Log::info('正在同步标签');
            foreach ($list['tags'] as $k => $v) {
                WechatTag::updateOrCreate([
                    'id' => $v['id'],
                    'name' => $v['name']
                ], [
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'count' => $v['count']
                ]);
            }
            Log::info('标签同步完成');

            return $this->message('同步完成');
        } catch (\Exception $exception) {
            return $this->failed('同步失败，请稍候重试');
        }
    }

    public function list()
    {
        return $this->success(WechatTag::all());
    }
}
