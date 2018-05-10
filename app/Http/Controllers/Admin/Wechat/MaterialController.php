<?php

namespace App\Http\Controllers\Admin\Wechat;

use App\Models\Wechat\WechatMaterial;
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

//            foreach ($news_list['item_count'] as $k => $v) {
//                WechatMaterial::updateOrCreate([
//                    'media_id' => $v['media_id']
//                ], [
//                    'media_id' => $v['media_id'],
//                    'content' => $v['content']
//                ]);
//            }

            $offset += $news_list['item_count'];
            $count = $news_list['total_count'] - $offset;
            Log::info($news_list);

        } while (true);
        Log::info('图文素材同步完成');

        Log::info('正在同步图片素材');
//        $offset = 0;
//        $count = 20;
//        do {
//            $image_list = $this->material->list('image', $offset, $count);
//
//            if ($image_list['item_count'] < 1) {
//                break;
//            }
//
//            foreach ($image_list['item_count'] as $k => $v) {
//                WechatMaterial::updateOrCreate([
//                    'media_id' => $v['media_id']
//                ], [
//                    'media_id' => $v['media_id'],
//                    'content' => array(
//                        'name' => $v['name'],
//                        'update_time' => $v['update_time'],
//                        'url' => $v['url']
//                    )
//                ]);
//            }
//
//            $offset += $image_list['item_count'];
//            $count = $image_list['total_count'] - $offset;
//            Log::info($image_list);
//
//        } while (true);
        Log::info('图片素材同步完成');

        Log::info('正在同步视频素材');
//        $offset = 0;
//        $count = 20;
//        do {
//            $video_list = $this->material->list('video', $offset, $count);
//
//            if ($video_list['item_count'] < 1) {
//                break;
//            }
//
//            foreach ($video_list['item_count'] as $k => $v) {
//                WechatMaterial::updateOrCreate([
//                    'media_id' => $v['media_id']
//                ], [
//                    'media_id' => $v['media_id'],
//                    'content' => array(
//                        'name' => $v['name'],
//                        'update_time' => $v['update_time'],
//                        'url' => $v['url']
//                    )
//                ]);
//            }
//
//            $offset += $video_list['item_count'];
//            $count = $video_list['total_count'] - $offset;
//            Log::info($video_list);
//
//        } while (true);
        Log::info('视频素材同步完成');

        Log::info('正在同步音频素材');
//        $offset = 0;
//        $count = 20;
//        do {
//            $voice_list = $this->material->list('voice', $offset, $count);
//
//            if ($voice_list['item_count'] < 1) {
//                break;
//            }
//
//            foreach ($voice_list['item_count'] as $k => $v) {
//                WechatMaterial::updateOrCreate([
//                    'media_id' => $v['media_id']
//                ], [
//                    'media_id' => $v['media_id'],
//                    'content' => array(
//                        'name' => $v['name'],
//                        'update_time' => $v['update_time'],
//                        'url' => $v['url']
//                    )
//                ]);
//            }
//
//            $offset += $voice_list['item_count'];
//            $count = $voice_list['total_count'] - $offset;
//            Log::info($voice_list);
//
//        } while (true);
        Log::info('音频素材同步完成');
    }
}
