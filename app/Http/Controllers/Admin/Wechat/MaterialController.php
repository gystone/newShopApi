<?php

namespace App\Http\Controllers\Admin\Wechat;

use App\Models\Wechat\WechatMaterial;
use EasyWeChat\Kernel\Messages\Article;
use EasyWeChat\OfficialAccount\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    private $material;
    
    public function __construct(Application $app)
    {
        auth()->shouldUse('api_admin');
        $this->material = $app->material;
    }

    public function materialSync()
    {
        Log::info('正在同步图片素材');
        $offset = 0;
        $count = 20;
        do {
            if ($count < 1) {
                break;
            }
            $image_list = $this->material->list('image', $offset, $count);

            foreach ($image_list['item'] as $k => $v) {
                $stream = $this->material->get($v['media_id']);
                $path = 'wechat/images/'.date('Y-m-d').'/'.md5($v['name'].$v['media_id']);
                if (Storage::disk('admin')->put($path, $stream)) {
                    WechatMaterial::updateOrCreate([
                        'media_id' => $v['media_id']
                    ], [
                        'media_id' => $v['media_id'],
                        'type' => 'image',
                        'content' => array(
                            'name' => $v['name'],
                            'update_time' => $v['update_time'],
                            'url' => $v['url'],
                            'path' => Storage::disk('admin')->url($path),
                        )
                    ]);
                }
            }

            $offset += $image_list['item_count'];
            $count = $image_list['total_count'] - $offset;
            Log::info($image_list);

        } while (true);
        Log::info('图片素材同步完成');

        Log::info('正在同步图文素材');
        $offset = 0;
        $count = 20;
        do {
            if ($count < 1) {
                break;
            }
            $news_list = $this->material->list('news', $offset, $count);

            foreach ($news_list['item'] as $k => $v) {
                $content = [];
                foreach ($v['content']['news_item'] as $k1 => $v1) {
                    $img = WechatMaterial::where('media_id', $v1['thumb_media_id'])->first();
                    $content['news_item'][] = array(
                        'title' => $v1['title'],
                        'digest' => $v1['digest'],
                        'author' => $v1['author'],
                        'content' => $v1['content'],
                        'content_source_url' => $v1['content_source_url'],
                        'thumb_media_id' => $v1['thumb_media_id'],
                        'show_cover_pic' => $v1['show_cover_pic'],
                        'url' => $v1['url'],
                        'thumb_url' => $v1['thumb_url'],
                        'thumb_path' => $img['content']['path'],
                        'need_open_comment' => $v1['need_open_comment'],
                        'only_fans_can_comment' => $v1['only_fans_can_comment'],
                    );
                }
                WechatMaterial::updateOrCreate([
                    'media_id' => $v['media_id']
                ], [
                    'media_id' => $v['media_id'],
                    'type' => 'news',
                    'content' => $content
                ]);
            }

            $offset += $news_list['item_count'];
            $count = $news_list['total_count'] - $offset;

        } while (true);
        Log::info('图文素材同步完成');



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
//                    'type' => 'video',
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
//                    'type' => 'voice',
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

    public function materialList(Request $request)
    {
        $type = $request->type;

        if (! in_array($type, ['news', 'image', 'video', 'voice'])) {
            return respond('非法访问！', 400);
        }

        return respond($type.'素材列表', 200, WechatMaterial::where('type', $type)->get());
    }

    public function materialDetail(WechatMaterial $wechatMaterial)
    {
        return respond('素材详情', 200, $wechatMaterial);
    }

    public function materialItemDetail(WechatMaterial $wechatMaterial, $index)
    {
        return respond('素材子项详情', 200, $wechatMaterial->content['news_item'][$index]);
    }

    public function materialNewsUpdate(WechatMaterial $wechatMaterial, $index, Request $request)
    {
        $content_tb = $request->only(['title', 'digest', 'author', 'content', 'content_source_url', 'thumb_media_id',
            'show_cover_pic', 'url', 'thumb_url', 'thumb_path', 'need_open_comment', 'only_fans_can_comment']);
dd($request->all());
        $article = new Article([
            'title' => $request->title,
            'author' => $request->author,
            'content' => $request->input('content'),
            'thumb_media_id' => $request->thumb_media_id,
            'digest' => $request->digest,
            'source_url' => $request->content_source_url,
            'show_cover' => $request->show_cover_pic,
        ]);

        $res = $this->material->updateArticle($wechatMaterial->media_id, $article, $index);

        if ($res) {
            $wechatMaterial->content['news_item'][$index] = $content_tb;
            $wechatMaterial->save();
            return respond('更新成功', 200, $wechatMaterial);
        } else {
            return respond('更新失败，请稍候重试', 200, $wechatMaterial);
        }
    }
}
