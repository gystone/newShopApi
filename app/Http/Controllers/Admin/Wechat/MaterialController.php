<?php

namespace App\Http\Controllers\Admin\Wechat;

use App\Http\Controllers\ApiController;
use App\Models\Wechat\WechatMaterial;
use EasyWeChat\Kernel\Messages\Article;
use EasyWeChat\OfficialAccount\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MaterialController extends ApiController
{
    private $material;
    
    public function __construct(Application $app)
    {
        auth()->shouldUse('api_admin');
        $this->material = $app->material;
    }

    /**
     * 同步素材
     * @method GET
     * @return \Illuminate\Http\JsonResponse
     */
    public function materialSync()
    {
        DB::beginTransaction();

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
                    $path = 'wechat/images/'.md5($v['media_id'].$v['name']);
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
                    $content['news_item'] = $this->getNewsItem($v['content']['news_item']);
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

            Log::info('正在同步视频素材');
            $offset = 0;
            $count = 20;
            do {
                $video_list = $this->material->list('video', $offset, $count);
                Log::info($video_list);
                if (!isset($video_list['item_count']) || $video_list['item_count'] < 1) {
                    break;
                }

                foreach ($video_list['item'] as $k => $v) {
                    $video_source = $this->material->get($v['media_id']);
                    $path = 'wechat/videos/'.pathinfo(parse_url($video_source['down_url'])['path'])['basename'];
                    if (Storage::disk('admin')->put($path, file_get_contents($video_source['down_url']))) {
                        $video_path = Storage::disk('admin')->url($path);
                        WechatMaterial::updateOrCreate([
                            'media_id' => $v['media_id']
                        ], [
                            'media_id' => $v['media_id'],
                            'type' => 'video',
                            'content' => array(
                                'name' => $video_source['title'],
                                'description' => $video_source['description'],
                                'update_time' => $v['update_time'],
                                'down_url' => $video_source['down_url'],
                                'path' => $video_path
                            )
                        ]);
                    }
                }

                $offset += $video_list['item_count'];
                $count = $video_list['total_count'] - $offset;

            } while (true);
            Log::info('视频素材同步完成');

            Log::info('正在同步音频素材');
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
                        WechatMaterial::updateOrCreate([
                            'media_id' => $v['media_id']
                        ], [
                            'media_id' => $v['media_id'],
                            'type' => 'voice',
                            'content' => array(
                                'name' => $v['name'],
                                'update_time' => $v['update_time'],
                                'path' => Storage::disk('admin')->url($path)
                            )
                        ]);
                    }
                }

                $offset += $voice_list['item_count'];
                $count = $voice_list['total_count'] - $offset;

            } while (true);
            Log::info('音频素材同步完成');

            DB::commit();

            return $this->message('同步成功');
        } catch (\Exception $exception) {Log::info($exception->getMessage());
            DB::rollBack();
            return $this->failed('同步失败，错误：'.$exception->getMessage());
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

    /**
     * 素材列表
     * type 素材类型：news,image,voice,video
     * @method GET
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function materialList(Request $request)
    {
        $type = $request->type;

        if (! in_array($type, ['news', 'image', 'video', 'voice'])) {
            return $this->failed('非法访问！', 400);
        }

        return $this->success(WechatMaterial::where('type', $type)->paginate(10));
    }

    /**
     * 素材详情
     * @method GET
     * @param WechatMaterial $wechatMaterial interger 素材id
     * @return \Illuminate\Http\JsonResponse
     */
    public function materialDetail(WechatMaterial $wechatMaterial)
    {
        return $this->success($wechatMaterial);
    }

    /**
     * 图文消息子项详情
     * @method GET
     * @param WechatMaterial $wechatMaterial interger 素材id
     * @param $index integer 图文索引
     * @return \Illuminate\Http\JsonResponse
     */
    public function materialItemDetail(WechatMaterial $wechatMaterial, $index)
    {
        return $this->success($wechatMaterial->content['news_item'][$index]);
    }

    /**
     * 更新图文素材
     * content['new_item'][] 消息内容 title,author,content,thumb_media_id,digest,source_url,show_cover
     * @method PATCH
     * @param WechatMaterial $wechatMaterial interger 素材id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function materialNewsUpdate(WechatMaterial $wechatMaterial, Request $request)
    {
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

        }

        $res_news = $this->material->get($wechatMaterial->media_id);
        if (isset($res_news['news_item'])) {
            $material_content['news_item'] = $this->getNewsItem($res_news['news_item']);
            $material_content['update_time'] = date('Y-m-d H:i:s');
            $wechatMaterial->content = $material_content;

            if ($wechatMaterial->save()) {
                return $this->success($wechatMaterial);
            } else {
                return $this->failed('更新失败，请稍候重试');
            }
        } else {
            return $this->failed('更新失败，请稍候重试');
        }

    }

    /**
     * 新增图文素材
     * content['new_item'][] 消息内容 title,author,content,thumb_media_id,digest,source_url,show_cover
     * @method POST
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function materialNewsUpload(Request $request)
    {
        $wechatMaterial = new WechatMaterial();

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
        }

        $res = $this->material->uploadArticle($article);
        if (isset($res['media_id'])) {
            $res_news = $this->material->get($res['media_id']);
            $material_content['news_item'] = $this->getNewsItem($res_news['news_item']);
            $material_content['update_time'] = date('Y-m-d H:i:s');
            $wechatMaterial->content = $material_content;
            $wechatMaterial->media_id = $res['media_id'];
            $wechatMaterial->type = 'news';

            if ($wechatMaterial->save()) {
                return $this->success($wechatMaterial);
            } else {
                return $this->failed('上传失败，请稍候重试');
            }
        } else {
            return $this->failed('上传失败，请稍候重试');
        }
    }

    /**
     * 上传图片素材
     * img JPG|PNG...
     * @method POST
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function materialImgUpload(Request $request)
    {
        $image = $request->file('img');

        $path = 'wechat/images/';

        if ($img_path = Storage::disk('admin')->put($path, $image)) {
            $res = $this->material->uploadImage('uploads/'.$img_path);
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
                        'path' => $path,
                        )
                ]);
                return $this->success($img_res);
            } else {
                return $this->failed('上传失败，请稍候重试');
            }

        }
    }

    public function materialVoiceUpload(Request $request)
    {
        $voice = $request->file('voice');

        $path = 'wechat/voices/';

        if ($voice_path = Storage::disk('admin')->put($path, $voice)) {
            $res = $this->material->uploadVoice('uploads/'.$voice_path);
            $path = Storage::disk('admin')->url($voice_path);

            if (isset($res['media_id'])) {
                $voice_res = WechatMaterial::updateOrCreate([
                    'media_id' => $res['media_id']
                ], [
                    'media_id' => $res['media_id'],
                    'type' => 'voice',
                    'content' => array(
                        'name' => $voice->getClientOriginalName(),
                        'update_time' => date('Y-m-d H:i:s'),
                        'path' => $path,
                    )
                ]);
                return $this->success($voice_res);
            } else {
                return $this->failed('上传失败，请稍候重试');
            }

        }
    }

    public function materialVideoUpload(Request $request)
    {
        $video = $request->file('video');
        $title = $request->title;
        $description = $request->description;

        $path = 'wechat/videos/';

        if ($video_path = Storage::disk('admin')->put($path, $video)) {
            $res = $this->material->uploadVideo('uploads/'.$video_path, $title, $description);
            $path = Storage::disk('admin')->url($video_path);

            if (isset($res['media_id'])) {
                $video_source = $this->material->get($res['media_id']);
                $video_res = WechatMaterial::updateOrCreate([
                    'media_id' => $res['media_id']
                ], [
                    'media_id' => $res['media_id'],
                    'type' => 'video',
                    'content' => array(
                        'name' => $title,
                        'description' => $description,
                        'update_time' => date('Y-m-d H:i:s'),
                        'down_url' => $video_source['down_url'] ?? '',
                        'path' => $path,
                    )
                ]);
                return $this->success($video_res);
            } else {
                return $this->failed('上传失败，请稍候重试');
            }

        }
    }

    /**
     * 删除素材
     * media_id
     * @method DELETE
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function materialDelete(Request $request)
    {
        $media_id = $request->media_id;

        $res = $this->material->delete($media_id);

        if ($res['errcode'] === 0) {
            WechatMaterial::where('media_id', $media_id)->delete();
            return $this->message('删除成功');
        } else {
            return $this->failed('删除失败，请稍候重试');
        }
    }
}
