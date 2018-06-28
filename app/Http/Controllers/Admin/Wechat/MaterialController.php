<?php

namespace App\Http\Controllers\Admin\Wechat;

use App\Http\Controllers\Admin\BaseController;
use App\Http\Requests\Wechat\MaterialImageRequest;
use App\Http\Requests\Wechat\MaterialNewsRequest;
use App\Http\Requests\Wechat\MaterialVideoRequest;
use App\Http\Requests\Wechat\MaterialVoiceRequest;
use App\Jobs\SyncWechatImage;
use App\Jobs\SyncWechatNews;
use App\Jobs\SyncWechatVideo;
use App\Jobs\SyncWechatVoice;
use App\Models\Wechat\WechatMaterial;
use EasyWeChat\Kernel\Messages\Article;
use EasyWeChat\OfficialAccount\Application;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MaterialController extends BaseController
{
    private $material;
    
    public function __construct(Application $app)
    {
        parent::__construct();
        $this->material = $app->material;
    }

    /**
     * 同步素材
     * @method GET
     * @return \Illuminate\Http\JsonResponse
     */
    public function materialSync()
    {
        try {
            // 清空素材表
            WechatMaterial::truncate();
            Log::info('正在同步图片素材');
            SyncWechatImage::dispatch();
            Log::info('图片素材同步完成');

            Log::info('正在同步视频素材');
            SyncWechatVideo::dispatch();
            Log::info('视频素材同步完成');

            Log::info('正在同步音频素材');
            SyncWechatVoice::dispatch();
            Log::info('音频素材同步完成');

            Log::info('正在同步图文素材');
            SyncWechatNews::dispatch()->delay(now()->addMinutes(2));
            Log::info('图文素材同步完成');

            return $this->message('同步成功');
        } catch (\Exception $exception) {Log::info($exception->getMessage());
            DB::rollBack();
            return $this->failed('同步失败，请稍候重试. 错误信息：'.$exception->getMessage());
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

        $list = WechatMaterial::where('type', $type)->latest('id');

        return $this->success($request->page ? $list->paginate($request->limit ?? 20) : $list->get());
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
    public function materialNewsUpdate(WechatMaterial $wechatMaterial, MaterialNewsRequest $request)
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
    public function materialNewsUpload(MaterialNewsRequest $request)
    {
        $wechatMaterial = new WechatMaterial();

        $material_content = $wechatMaterial->content;
        foreach ($request->input('content.news_item') as $k => $v) {
            $article[] = new Article([
                'title' => $v['title'],
                'author' => $v['author'],
                'content' => $v['content'],
                'thumb_media_id' => $v['thumb_media_id'],
                'digest' => $v['digest'] ?? null,
                'source_url' => $v['content_source_url'] ?? null,
                'show_cover' => $v['show_cover_pic'],
            ]);
        }

        $res = $this->material->uploadArticle($article);Log::info($res);
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
    public function materialImgUpload(MaterialImageRequest $request)
    {
        $image = $request->file('img');
        $title = $request->title;

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
                        'name' => $title ?? $image->getClientOriginalName(),
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

    public function materialVoiceUpload(MaterialVoiceRequest $request)
    {
        $voice = $request->file('voice');
        $title = $request->title;

        $path = 'wechat/voices/';
        if ($voice_path = Storage::disk('admin')->put($path, $voice)) {
            if (pathinfo($voice_path, PATHINFO_EXTENSION) == 'mpga') {
                $new_path = $path.pathinfo($voice_path, PATHINFO_FILENAME).'.mp3';
                Storage::disk('admin')->move($voice_path, $new_path);
                $voice_path = $new_path;
            }
            $res = $this->material->uploadVoice('uploads/'.$voice_path);
            $path = Storage::disk('admin')->url($voice_path);

            if (isset($res['media_id'])) {
                $voice_res = WechatMaterial::updateOrCreate([
                    'media_id' => $res['media_id']
                ], [
                    'media_id' => $res['media_id'],
                    'type' => 'voice',
                    'content' => array(
                        'name' => $title ?? $voice->getClientOriginalName(),
                        'update_time' => date('Y-m-d H:i:s'),
                        'path' => $path,
                    )
                ]);
                return $this->success($voice_res);
            } else {Log::info($res);
                return $this->failed('上传失败，请稍候重试');
            }

        }
    }

    public function materialVideoUpload(MaterialVideoRequest $request)
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

    public function search($type)
    {
        $content = trim(\request()->get('content'));

        switch ($type) {
            case 'news':
                $news_list = WechatMaterial::where('type', 'news')->latest('id')->get();
                $data = [];
                foreach ($news_list as $k => $v) {
                    foreach ($v['content']['news_item'] as $k1 => $v1) {
                        if (stripos($v1['title'], $content) !== false) {
                            $data[] = $v;
                        }
                    }
                }

                // 结果去重
                $res_data = [];
                foreach (array_unique($data) as $item) {
                    $res_data[] = json_decode($item, true);
                }
                $res = $this->success(\request('page') ? $this->paginated($res_data, \request('limit') ?? 20) : $res_data);
                break;
            case 'image':
                $image_list = WechatMaterial::where('type', 'image')->latest('id')->get();
                $data = $this->searchOther($image_list, $content);
                $res = $this->success(\request('page') ? $this->paginated($data, \request('limit') ?? 20) : $data);
                break;
            case 'video':
                $video_list = WechatMaterial::where('type', 'video')->latest('id')->get();
                $data = $this->searchOther($video_list, $content);
                $res = $this->success(\request('page') ? $this->paginated($data, \request('limit') ?? 20) : $data);
                break;
            case 'voice':
                $voice_list = WechatMaterial::where('type', 'voice')->latest('id')->get();
                $data = $this->searchOther($voice_list, $content);
                $res = $this->success(\request('page') ? $this->paginated($data, \request('limit') ?? 20) : $data);
                break;
            default:
                $res = $this->failed('非法访问');
        }

        return $res;
    }

    private function searchOther($list, $content)
    {
        $data = [];
        foreach ($list as $k => $v) {
            if (stripos($v['content']['name'], $content) !== false) {
                $data[] = $v;
            }
        }
        return $data;
    }

    private function paginated($data, $num)
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage() - 1;

        $collection = new Collection($data);

        $perPage = $num;

        $currentPageSearchResults = $collection->slice($currentPage * $perPage, $perPage)->all();

        $paginatedSearchResults= new LengthAwarePaginator($currentPageSearchResults, count($collection), $perPage);

        return $paginatedSearchResults->data();
    }
}
