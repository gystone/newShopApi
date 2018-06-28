<?php

namespace App\Service\Wechat;

use App\Models\Wechat\WechatMaterial;
use EasyWeChat\OfficialAccount\Application;
use Illuminate\Support\Facades\Storage;

class WechatMaterialService
{
    protected $material;

    public function __construct(Application $application)
    {
        $this->material = $application->material;
    }

    public function syncImage()
    {
        $material_images = [];
        $offset = 0;
        $count = 20;
        do {
            if ($count < 1) {
                break;
            }
            $image_list = $this->material->list('image', $offset, $count);

            foreach ($image_list['item'] as $k => $v) {
                $stream = $this->material->get($v['media_id']);
                $path = 'wechat/images/'.md5($v['media_id'].$v['name']);
                if (Storage::disk('admin')->put($path, $stream)) {
                    $material_images[] = [
                        'media_id' => $v['media_id'],
                        'type' => 'image',
                        'content' => serialize(array(
                            'name' => $v['name'],
                            'update_time' => date('Y-m-d H:i:s', $v['update_time']),
                            'url' => $v['url'],
                            'path' => Storage::disk('admin')->url($path),
                        ))
                    ];
                }
            }

            $offset += $image_list['item_count'];
            $count = $image_list['total_count'] - $offset;
            if ($count <= 0) {
                break;
            }

        } while (true);

        // 图片素材插入数据库
        if ($material_images) {
            WechatMaterial::insert($material_images);
        }
    }

    public function syncNews()
    {
        $material_news = [];
        $offset = 0;
        $count = 20;
        do {
            if ($count < 1) {
                break;
            }
            $news_list = $this->material->list('news', $offset, $count);

            foreach ($news_list['item'] as $k => $v) {
                $content = [];
                $content['news_item'] = $this->getNewsItem($v['content']['news_item']);
                $content['update_time'] = date('Y-m-d H:i:s', $v['update_time']);

                $material_news[] = [
                    'media_id' => $v['media_id'],
                    'type' => 'news',
                    'content' => serialize($content)
                ];
            }

            $offset += $news_list['item_count'];
            $count = $news_list['total_count'] - $offset;
            if ($count <= 0) {
                break;
            }

        } while (true);

        if ($material_news) {
            WechatMaterial::insert($material_news);
        }
    }

    public function syncVideo()
    {
        $material_videos = [];
        $offset = 0;
        $count = 20;
        // 设置超时参数
        $opts = array(
            "http" => array(
                "method" => "GET",
                "timeout" => 10
            ),
        );
        // 创建数据流上下文
        $context = stream_context_create($opts);

        do {
            $video_list = $this->material->list('video', $offset, $count);

            if (!isset($video_list['item_count']) || $video_list['item_count'] < 1) {
                break;
            }

            foreach ($video_list['item'] as $k => $v) {
                $video_source = $this->material->get($v['media_id']);
                $path = 'wechat/videos/'.pathinfo(parse_url($video_source['down_url'])['path'])['basename'];
                if (Storage::disk('admin')->put($path, file_get_contents($video_source['down_url'], false, $context))) {
                    $video_path = Storage::disk('admin')->url($path);
                    $material_videos[] = [
                        'media_id' => $v['media_id'],
                        'type' => 'video',
                        'content' => serialize(array(
                            'name' => $video_source['title'],
                            'description' => $video_source['description'],
                            'update_time' => date('Y-m-d H:i:s', $v['update_time']),
                            'down_url' => $video_source['down_url'],
                            'path' => $video_path
                        ))
                    ];
                }
            }

            $offset += $video_list['item_count'];
            $count = $video_list['total_count'] - $offset;
            if ($count <= 0) {
                break;
            }

        } while (true);

        if ($material_videos) {
            WechatMaterial::insert($material_videos);
        }
    }

    public function syncVoice()
    {
        $material_voices = [];
        $offset = 0;
        $count = 20;
        do {
            $voice_list = $this->material->list('voice', $offset, $count);

            if (!isset($voice_list['item_count']) || $voice_list['item_count'] < 1) {
                break;
            }

            foreach ($voice_list['item'] as $k => $v) {
                $stream = $this->material->get($v['media_id']);
                $path = 'wechat/voices/'.$v['name'];
                if (Storage::disk('admin')->put($path, $stream)) {
                    $material_voices[] = [
                        'media_id' => $v['media_id'],
                        'type' => 'voice',
                        'content' => serialize(array(
                            'name' => $v['name'],
                            'update_time' => date('Y-m-d H:i:s', $v['update_time']),
                            'path' => Storage::disk('admin')->url($path)
                        ))
                    ];
                }
            }

            $offset += $voice_list['item_count'];
            $count = $voice_list['total_count'] - $offset;
            if ($count <= 0) {
                break;
            }

        } while (true);

        if ($material_voices) {
            WechatMaterial::insert($material_voices);
        }
    }

    /**
     * 重构图文素材数据
     * @param $news_item array|object
     * @return array
     */
    private function getNewsItem($news_item) {
        foreach ($news_item as $k1 => $v1) {
            $img = WechatMaterial::where('media_id', $v1['thumb_media_id'])->first();
            $res_news_item[] = array(
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
        return $res_news_item;
    }
}