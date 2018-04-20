<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\WechatArticle;

use App\Models\Wechat\WechatImage;
use EasyWeChat\Kernel\Messages\Article;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\Log;
use Encore\Admin\Auth\Permission;
class WechatArticleController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        Permission::check('wechat');
        return Admin::content(function (Content $content) {

            $content->header('文章管理');
            $content->description('列表');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        Permission::check('wechat');
        return Admin::content(function (Content $content) use ($id) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        Permission::check('wechat');
        return Admin::content(function (Content $content) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(WechatArticle::class, function (Grid $grid) {

            $grid->id('ID')->sortable();

            $grid->title('标题');
            $grid->author('作者');
            $grid->img()->path('封面图片')->image(36, 36);
            $grid->digest('摘要');
            $grid->content('内容')->expand(function () {
                return $this->content;
            }, '内容详情');
            $grid->source_url('来源');
            $grid->show_cover('显示封面')->switch([
                'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
            ]);
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(WechatArticle::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->text('title', '标题');
            $form->text('author', '作者');
            $form->select('thumb_media_id', '封面图片')->options(function () {
                $arr = [];
                $imgs = WechatImage::all();
                foreach ($imgs as $k => $v) {
                    $arr[$v->media_id] = $v->name;
                }
                return $arr;
            });
            $form->textarea('digest', '摘要');
            $form->editor('content', '内容');
            $form->text('source_url', '来源');
            $form->switch('show_cover', '显示封面')->states([
                'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
            ]);

            $form->saved(function (Form $form) {
                $app = app('wechat.official_account');
                $article = new Article([
                    'title' => $form->model()->title,
                    'author' => $form->model()->author,
                    'content' => $form->model()->content,
                    'thumb_media_id' => $form->model()->thumb_media_id,
                    'digest' => $form->model()->digest,
                    'source_url' => $form->model()->source_url ?? '',
                    'show_cover' => $form->model()->show_cover
                ]);
                if (!isset($form->model()->media_id)) {
                    Log::info('upload');
                    $res = $app->material->uploadArticle($article);
                } else {
                    Log::info('update');
                    $res = $app->material->updateArticle($form->model()->media_id, $article);
                }

                if (isset($res['media_id'])) {
                    Log::info('文章素材上传成功');
                    $article = $app->material->get($res['media_id']);
                    Log::info($article);
                    $image = WechatArticle::find($form->model()->id);
                    $image->update(['media_id' => $res['media_id'], 'url' => $article['news_item'][0]['url']]);
                } else {
                    Log::info('文章素材更新',$res);
                }
            });
        });
    }
}
