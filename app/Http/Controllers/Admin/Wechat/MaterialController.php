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
        try {
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
                                'update_time' => date('Y-m-d H:i:s', $v['update_time']),
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
                    $content['update_time'] = date('Y-m-d H:i:s', $v['update_time']);

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

//            Log::info('正在同步视频素材');
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
//            Log::info('视频素材同步完成');
//
//            Log::info('正在同步音频素材');
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
//            Log::info('音频素材同步完成');

            return respond('同步成功');
        } catch (\Exception $exception) {
            return respond('同步失败，错误：'.$exception->getMessage());
        }
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

    public function materialNewsUpdate(WechatMaterial $wechatMaterial, Request $request)
    {
        $content = [];
        $wechatMaterial->content = ['news_item' => []];
        $material_content = $wechatMaterial->content;
        foreach ($request->input('content.news_item') as $k => $v) {
            $article = new Article([
                'title' => $v['title'],
                'author' => $v['author'],
                'content' => $v['content'],
                'thumb_media_id' => $v['thumb_media_id'],
                'digest' => $v['digest'],
                'source_url' => $v['content_source_url'],
                'show_cover' => $v['show_cover_pic'],
            ]);

            $res = $this->material->updateArticle($wechatMaterial->media_id, $article, $k);

            if ($res['errcode'] === 0) {
                $content = array(
                    'title' => $v['title'],
                    'digest' => $v['digest'],
                    'author' => $v['author'],
                    'content' => $v['content'],
                    'content_source_url' => $v['content_source_url'],
                    'thumb_media_id' => $v['thumb_media_id'],
                    'show_cover_pic' => $v['show_cover_pic'],
                    'url' => $v['url'],
                    'thumb_url' => $v['thumb_url'],
                    'thumb_path' => $v['thumb_path'],
                    'need_open_comment' => $v['need_open_comment'],
                    'only_fans_can_comment' => $v['only_fans_can_comment']
                );
                $material_content['news_item'][] = $content;
            }
        }

        $material_content['update_time'] = date('Y-m-d H:i:s');
        $wechatMaterial->content = $material_content;

        if ($wechatMaterial->save()) {
            return respond('更新成功', 200, $wechatMaterial);
        } else {
            return respond('更新失败，请稍候重试', 200, $wechatMaterial);
        }
    }

    public function materialNewsUpload(Request $request)
    {
        $wechatMaterial = new WechatMaterial();
        $content = [];
        $wechatMaterial->content = ['news_item' => []];
        $material_content = $wechatMaterial->content;
        foreach ($request->input('content.news_item') as $k => $v) {
            $article[] = new Article([
                'title' => $v['title'],
                'author' => $v['author'],
                'content' => $v['content'],
                'thumb_media_id' => $v['thumb_media_id'],
                'digest' => $v['digest'],
                'source_url' => $v['content_source_url'],
                'show_cover' => $v['show_cover_pic'],
            ]);

            $content = array(
                'title' => $v['title'],
                'digest' => $v['digest'],
                'author' => $v['author'],
                'content' => $v['content'],
                'content_source_url' => $v['content_source_url'],
                'thumb_media_id' => $v['thumb_media_id'],
                'show_cover_pic' => $v['show_cover_pic'],
                'url' => $v['url'],
                'thumb_url' => $v['thumb_url'],
                'thumb_path' => $v['thumb_path'],
                'need_open_comment' => $v['need_open_comment'],
                'only_fans_can_comment' => $v['only_fans_can_comment']
            );
            $material_content['news_item'][] = $content;
        }

        $res = $this->material->uploadArticle($article);
        if (isset($res['media_id'])) {
            $material_content['update_time'] = date('Y-m-d H:i:s');
            $wechatMaterial->content = $material_content;
            $wechatMaterial->media_id = $res['media_id'];

            if ($wechatMaterial->save()) {
                return respond('上传成功', 200, $wechatMaterial);
            } else {
                return respond('上传失败，请稍候重试');
            }
        } else {
            return respond('上传失败，请稍候重试');
        }
    }

    public function materialImgUpload(Request $request)
    {
        $image = $request->file('img');

        $path = 'wechat/images/'.date('Y-m-d');

        if ($img_path = Storage::disk('admin')->put($path, $image)) {
            $res = $this->material->uploadImage('uploads/'.$img_path);Log::info($res);Log::info($path);
            $path = Storage::disk('admin')->url($img_path);

            if (isset($res['media_id'])) {
                $img_res = WechatMaterial::updateOrCreate([
                    'media_id' => $res['media_id']
                ], [
                    'media_id' => $res['media_id'],
                    'type' => 'image',
                    'content' => array(
                        'name' => $image->getClientOriginalName(),
                        'update_time' => date('Y-m-d H:i:s'),
                        'url' => $res['url'],
                        'path' =>$path,
                        )
                ]);
                return respond('上传成功', 200, $img_res);
            } else {
                return respond('上传失败，请稍候重试');
            }

        }
    }

    public function materialDelete(Request $request)
    {
        $media_id = $request->media_id;

        $res = $this->material->delete($media_id);

        if ($res['errcode'] === 0) {
            return respond('删除成功');
        }
    }
}
