<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\WechatKeyword;

use App\Models\Wechat\WechatNews;
use App\Models\Wechat\WechatText;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Auth\Permission;
class WechatKeywordController extends Controller
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

            $content->header('关键字');
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

            $content->header('关键字');
            $content->description('新建');

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
        return Admin::grid(WechatKeyword::class, function (Grid $grid) {

            $grid->id('关键字ID')->sortable();

            $grid->key_text('关键字');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(WechatKeyword::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->text('key_text', '关键字');
            $form->radio('msg_type', '消息类型')->options(['text' => '文本', 'news'=> '图文'])->default('text');
            $form->select('text_id', '文本消息')->options(function () {
                $arr = [];
                $texts = WechatText::all();
                foreach ($texts as $k => $v) {
                    $arr[$v->id] = mb_substr($v->content, 0, 30).'...';
                }
                return $arr;
            });
            $form->select('news_id', '图文消息')->options(function () {
                $arr = [];
                $news = WechatNews::where('pid', 0)->get();
                foreach ($news as $k => $v) {
                    $arr[$v->id] = mb_substr($v->title, 0, 30).'...';
                }
                return $arr;
            });

            Admin::script(<<<EOT
function type_init() {
    if($('input[name="msg_type"]:checked').val() == 'news') {
        $(".text_id").parent().parent().hide()
        $(".news_id").parent().parent().show()
    } else if($('input[name="msg_type"]:checked').val() == 'text') {
        $(".text_id").parent().parent().show()
        $(".news_id").parent().parent().hide()
    }
}
$(document).ready(function(){
    type_init(); 
    $('ins').removeAttr("style");
    $('.radio-inline').on('click', function () {
        type_init();
    })
});
EOT
            );

            $form->saving(function (Form $form) {
                switch ($form->msg_type) {
                    case 'text':
                        unset($form->news_id);
                        break;
                    case 'news':
                        unset($form->text_id);
                        break;
                }
            });
            
        });
    }
}
