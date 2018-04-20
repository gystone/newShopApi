<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\WechatUser;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Auth\Permission;
class WechatUserController extends Controller
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

            $content->header('微信用户');
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
        return Admin::grid(WechatUser::class, function (Grid $grid) {

            $grid->headimgurl('头像')->image(36,36);
            $grid->nickname('昵称');
            $grid->sex('性别')->display(function ($sex) {
                return $sex == 0 ? '女' : '男';
            });
            $grid->column('P-C', '省市')->display(function () {
                return $this->province . $this->city;
            });
            $grid->column('types', '角色类型')->display(function ($types) {
                $arr = ['0' => '普通', '1' => '客服', '2' => '医生', '3' => '发货', '4' => '财务', '5' => '门诊医生'];
                return $arr[$types];
            });
            $grid->status('关注状态')->display(function ($status) {
                return $status == 'subscribe' ? '<a class="btn btn-warning btn-xs">已关注</a>' : '<a class="btn btn-danger btn-xs">已取关</a>';
            });

            $grid->subscribe_time('关注时间');

            $grid->filter(function($filter){

                // 去掉默认的id过滤器
                $filter->disableIdFilter();

                // 在这里添加字段过滤器
                $filter->like('nickname', '昵称');

            });

            $grid->tools(function ($tools) {
                $tools->append('<a id="get-wechat-users" class="btn btn-success btn-sm fa fa-wechat"> 同步粉丝</a>');
            });

            Admin::script(<<<EOT
$(document).ready(function() {
    $('#get-wechat-users').on('click', function() {
        swal({
          title: "确认同步粉丝？",
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
                url: '/wechat/get_user',
                data: {
                    _token:LA.token
                },
                success: function (res) {
                    if(res == 'Ok') {
                        toastr.success('同步成功');
                        $.pjax.reload('#pjax-container');
                    } else {
                        toastr.warning('同步失败, 请重试')
                    }
                }
            });
        });
    })
});
EOT
);

            $grid->disableCreation();
            $grid->disableActions();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(WechatUser::class, function (Form $form) {

            $form->display('types');
        });
    }
}
