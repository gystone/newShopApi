<?php

namespace App\Http\Controllers\Admin\Wechat;

use EasyWeChat\OfficialAccount\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class MaterialController extends Controller
{
    private $material;
    
    public function __construct(Application $app)
    {
        $this->material = $app->material;
    }

    public function materialSync()
    {
        Log::info('正在同步图文素材');
        $offset = 0;
        $count = 20;
        do {
            $news_list = $this->material->list('news', $offset, $count);

            if ($news_list['item_count'] < 1) {
                break;
            }

            $offset += $news_list['item_count'];
            Log::info($news_list);

        } while (true);
        Log::info('图文素材同步完成');

        Log::info('正在同步图片素材');
        Log::info('图片素材同步完成');

        Log::info('正在同步视频素材');
        Log::info('视频素材同步完成');

        Log::info('正在同步音频素材');
        Log::info('音频素材同步完成');
    }
}
