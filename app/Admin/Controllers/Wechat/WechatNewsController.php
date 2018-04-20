<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\WechatNews;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Layout\Row;
use Encore\Admin\Tree;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Auth\Permission;
class WechatNewsController extends Controller
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

            $content->header('header');

            $content->row(function (Row $row) {
                $row->column(6, $this->menuTree());
                $row->column(6, function (Column $column) {
                    $form = new \Encore\Admin\Widgets\Form();
                    $form->action('wechat_news');
                    $form->select('pid', '封面')->options(function () {
                        $menu0 = WechatNews::where('pid', 0)->get();
                        $res = [0 => '无'];
                        foreach ($menu0 as $k => $v) {
                            $res[$v->id] =$v->title;
                        }
                        return $res;
                    });
                    $form->text('title', '标题');
                    $form->textarea('description', '描述');
                    $form->image('image', '图片')->removable()->move('wechat');
                    $form->text('url', '链接');
                    $form->hidden('id');
                    $column->append((new Box('图文消息', $form))->style('success'));
                });
            });
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
        return Admin::grid(WechatNews::class, function (Grid $grid) {

            $grid->id('ID')->sortable();

            $grid->title('标题');
            $grid->description('描述');
            $grid->image('图片')->image(36, 36);
            $grid->url('链接');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(WechatNews::class, function (Form $form) {

            $form->select('pid', '封面')->options(function () {
                $news0 = WechatNews::where('pid', 0)->get();
                $res = [0 => '无'];
                foreach ($news0 as $k => $v) {
                    $res[$v->id] =$v->title;
                }
                return $res;
            });
            $form->hidden('id');
            $form->text('title', '标题');
            $form->textarea('description', '描述');
            $form->image('image', '图片')->removable()->move('wechat');
            $form->text('url', '链接');

            $form->saving(function (Form $form) {
                $news0 = WechatNews::where('pid', $form->pid)->get();
                if (count($news0) >= 7) {
                    admin_toastr('多图文消息最多8条', 'error');
                    return back()->withInput();
                }
            });
        });
    }

    public function menuTree()
    {
//        Admin::script(<<<EOT
//$('.dd-nodrag').attr('style', 'position: relative; top: -27px;');
//console.log($('button'));
//$('button').parent('.dd-item').attr('style', 'margin-top:20px');
//EOT
//        );
        return WechatNews::tree(function (Tree $tree) {
            $tree->disableCreate();
            $tree->disableSave();
            $tree->disableRefresh();
            $tree->branch(function ($branch) {
                $src = url('upload/'.$branch['image']);
                $img = "<img src='$src' style='border:1px solid;width:36px;height:36px;' class='img'/>";

                return "<table style='width: 80%;display:table-cell'><tr><td style='width: 80px' rowspan='2'>$img</td><td style='font-size: 18px'>{$branch['title']}</td></tr><tr><td>{$branch['url']}</td></tr></table>";
            });
        });
    }
}
