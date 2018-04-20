<?php

namespace App\Admin\Controllers\Wechat;

use App\Admin\Extensions\Tools\AddKF;
use App\Models\Wechat\WechatKF;

use Encore\Admin\Auth\Permission;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class KFController extends Controller
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

            $content->header('公众号客服');
            $content->description('列表');

            $content->body($this->grid());
        });
    }

    public function edit($id)
    {
        Permission::check('wechat');
        return Admin::content(function (Content $content) use ($id) {

            $content->header('公众号客服');
            $content->description('编辑');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(WechatKF::class, function (Grid $grid) {

            $grid->id('客服ID')->sortable();

            $grid->kf_account('客服账号');
            $grid->kf_headimgurl('头像')->image(36, 36);
            $grid->kf_nick('昵称');

            $grid->disableCreation();

            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
                $tools->append('<a id="get_kf_list" class="btn btn-sm btn-warning"><i class="fa fa-get-pocket"> 获取客服列表</i></a>');
                $tools->append(new AddKF());
            });

            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->append("<a id='del_kf' class='btn btn-xs btn-danger' data-id='{$actions->getKey()}'><i class='fa fa-trash'></i></a>");
            });

            Admin::script(<<<EOT
$(document).on('click', '#del_kf', function() {
    var id = $(this).attr('data-id')
    console.log($(this).attr('data-id'))
    swal({
        title: "确认要删除该客服？",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "确认",
        closeOnConfirm: true,
        cancelButtonText: "取消"
        },
        function(){
            $.ajax({
            method: 'delete',
            url: '/wechat/del_kf',
            data: {
                _token:LA.token,
                id: id
            },
            success: function (result) {
                console.log(result)
                if(result.error === 0) {
                    $.pjax.reload('#pjax-container');
                    toastr.success(result.msg);
                } else {
                    toastr.warning(result.msg);
                }
            }
        });
    });    
});
            
$('#get_kf_list').on('click', function() {
    swal({
          title: "确认要获取客服列表？",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "确认",
          closeOnConfirm: true,
          cancelButtonText: "取消"
        },
        function(){
            $.ajax({
                method: 'get',
                url: '/wechat/get_kf_list',
                data: {
                    _token:LA.token
                },
                success: function (result) {
                    console.log(result)
                    if(result.error === 0) {
                        $.pjax.reload('#pjax-container');
                        toastr.success(result.msg);
                    } else {
                        toastr.warning(result.msg);
                    }
                }
            });
        });
});
EOT
);
        });
    }

    protected function form()
    {
        return Admin::form(WechatKF::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->text('kf_nick', '客服昵称');
            $form->image('kf_headimgurl', '客服头像')->help('支持jpg, png格式，图片大小不超过5M，建议上传正方形图片');

            $app = app('wechat.official_account')->customer_service;
            $form->saving(function (Form $form) use ($app) {
                if ($form->kf_nick && $form->kf_nick !== $form->model()->kf_nick) {
                    Log::info($app->update($form->model()->kf_account, $form->kf_nick));
                }

            });

            $form->saved(function (Form $form) use ($app) {
                if ($form->kf_headimgurl) {
                    Log::info($app->setAvatar($form->model()->kf_account, 'uploads/'.$form->model()->kf_headimgurl));
                    $client = new Client();
                    $client->get(url('wechat/get_kf_list'));
                }
            });
        });
    }
}
