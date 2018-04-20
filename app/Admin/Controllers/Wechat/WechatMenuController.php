<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\WechatArticle;
use App\Models\Wechat\WechatMenu;

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
use Illuminate\Support\Facades\Log;
use Encore\Admin\Auth\Permission;
class WechatMenuController extends Controller
{
    use ModelForm;

    public function __construct()
    {
        Admin::script(<<<EOT
function type_init() {
    if($('input[name="select_type"]:checked').val() == '0') {
        $(".menu_url").parent().parent().hide()
        $("#menu_key").parent().parent().parent().show()
    } else if($('input[name="select_type"]:checked').val() == '1') {
        $(".menu_url").parent().parent().show()
        $("#menu_key").parent().parent().parent().hide()
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
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        Permission::check('wechat');
        return Admin::content(function (Content $content) {

            $content->header('自定义菜单');

            $content->row(function (Row $row) {
                $row->column(6, $this->menuTree());
                $row->column(6, function (Column $column) {
                    $form = new \Encore\Admin\Widgets\Form();
                    $form->action('wechat_menu');
                    $form->select('pid')->options(function () {
                        $menu0 = WechatMenu::where('pid', 0)->get();
                        $res = [0 => '无'];
                        foreach ($menu0 as $k => $v) {
                            $res[$v->id] =$v->title;
                        }
                        return $res;
                    });
                    $form->text('title', '菜单名');
//                    $form->select('type', '类别')->options(['view' => '链接', 'click' => '单击']);
                    $form->display('type', '类别')->default('view')->value('view');
                    $form->radio('select_type', '输入方式')->options(['0' => '直接输入', '1' => '图文素材']);
                    $form->text('menu_key', '链接')->default('https://example.com');
                    $form->select('menu_url', '选择图文')->options(function () {
                        $arr = [];
                        $article = WechatArticle::all();
                        foreach ($article as $k => $v ) {
                            $arr[$v->url] = $v->title;
                        }
                        return $arr;
                    });

                    $form->hidden('id');
                    $form->hidden('menu_key');
                    $column->append((new Box('菜单项', $form))->style('success'));

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

            $content->header('自定义菜单');
            $content->description('编辑');

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
        return Admin::grid(WechatMenu::class, function (Grid $grid) {

            $grid->id('ID')->sortable();

            $grid->created_at();
            $grid->updated_at();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        return Admin::form(WechatMenu::class, function (Form $form) {

            $form->select('pid')->options(function () {
                $menu0 = WechatMenu::where('pid', 0)->get();
                $res = [0 => '无'];
                foreach ($menu0 as $k => $v) {
                    $res[$v->id] = $v->title;
                }
                return $res;
            });
            $form->text('title', '菜单名');
//            $form->select('type', '类别')->options(['view' => '链接', 'click' => '单击']);
            $form->display('type', '类别')->default('view')->value('view');
            $form->radio('select_type', '输入方式')->options(['0' => '直接输入', '1' => '图文素材']);

            $form->text('menu_key', '链接')->default('https://example.com');
            $form->select('menu_url', '选择图文')->options(function () {
                $arr = [];
                $article = WechatArticle::all();
                foreach ($article as $k => $v ) {
                    $arr[$v->url] = $v->title;
                }
                return $arr;
            });

            $form->hidden('id');
            $form->hidden('order');
            $form->saving(function (Form $form) {
                $form->type = 'view';
                if (!isset($form->model()->id)) {
                    $menu = WechatMenu::where('pid', $form->pid)->get();
                    if ($form->pid == 0 && count($menu) == 3) {
                        admin_toastr('不能再添加顶级菜单了', 'warning');
                        return back()->withInput();
                    } elseif (count($menu) == 5) {
                        admin_toastr('超出限额了', 'warning');
                        return back()->withInput();
                    } else {
                        $form->id = (count($menu) + 1) + $form->pid * 10;
                        $form->order = count($menu) + 1;
                    }
                }
                switch ($form->select_type) {
                    case '0':
                        $form->menu_url = null;
                        break;
                    case '1':
                        $form->menu_key = null;
                        break;
                }
                Log::info(\request()->all());
                Log::info($form->model()->menu_key);
            });
        });
    }

    public function menuTree()
    {
        return WechatMenu::tree(function (Tree $tree) {
            $tree->tools(function ($tools) {
                $tools->add('<a class="btn btn-warning btn-sm pull-left menu-del"><i class="fa fa-trash"> 删除菜单</i></a>');
                $tools->add('<a class="btn btn-success btn-sm pull-right menu-publish"><i class="fa fa-send"> 发布菜单</i></a>');
            });
            $tree->disableCreate();
            $tree->disableSave();
            $tree->disableRefresh();
            $tree->branch(function ($branch) {
                return "{$branch['title']}";
            });

            Admin::script(<<<EOT
$(document).ready(function() {
    $('.menu-del').on('click', function() {
        swal({
              title: "确认删除菜单？",
              type: "warning",
              showCancelButton: true,
              confirmButtonColor: "#DD6B55",
              confirmButtonText: "确定",
              closeOnConfirm: true,
              cancelButtonText: "取消"
            },
            function(){
                $.ajax({
                    method: 'get',
                    url: '/wechat/del_menu',
                    data: {
                        _token:LA.token
                    },
                    success: function (res) {
                        if(res.errcode == 0) {
                            toastr.success('删除成功');
                            $.pjax.reload('#pjax-container');
                        } else {
                            toastr.warning('删除失败, 请重试')
                        }
                    }
                });
            })
    })
    
    $('.menu-publish').on('click', function() {
        $.ajax({
            method: 'get',
            url: '/wechat/publish_menu',
            data: {
                _token:LA.token
            },
            success: function (res) {
                if(res.errcode == 0) {
                    toastr.success('发布成功');
                } else {
                    toastr.warning('发布失败, 请重试')
                }
            }
        });
    })
});
EOT
);
        });
    }
}
