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
        $list = $this->tag->list();

        try {
            Log::info('正在同步标签');
            foreach ($list as $k => $v) {
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
        } catch (\Exception $exception) {
            $this->failed('同步失败，请稍候重试');
        }

        return $this->message('同步完成');
    }
}
