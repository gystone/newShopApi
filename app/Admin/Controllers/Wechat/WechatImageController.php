<?php

namespace App\Admin\Controllers\Wechat;

use App\Admin\Extensions\Tools\GridView;
use App\Models\Wechat\WechatImage;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Encore\Admin\Auth\Permission;
class WechatImageController extends Controller
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

            $content->header('图片管理');
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
        return Admin::grid(WechatImage::class, function (Grid $grid) {

            $grid->id('ID')->sortable();

            $grid->name('名称');
            $grid->path('图片')->image();

            $grid->tools(function ($tools) {
                $tools->append(new GridView());
            });

            if (Request::get('view') !== 'table') {
                $grid->setView('admin.grid.card');
            }
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(WechatImage::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->text('name', '名称');
            $form->image('path', '图片')->move('wechat');

            $form->saved(function (Form $form) {
                Log::info($form->model()->path);
                $app = app('wechat.official_account');
                if (!isset($form->model()->media_id)) {
                    $result = $app->material->uploadImage('upload/' . $form->model()->path);
                }

                if (isset($result['media_id']) && isset($result['url'])) {
                    Log::info('图片素材上传成功');
                    $image = WechatImage::find($form->model()->id);
                    $image->update(['media_id' => $result['media_id'], 'url' => $result['url']]);
                }
            });
        });
    }
}
