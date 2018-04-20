<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class AddKF extends AbstractTool
{
    public function script()
    {
        return <<<EOT
$('#add_kf').on('click', function() {
    $('#new_kf').modal('show')
})
$('#save_kf').on('click', function() {
    if($('#inputNewKFA').val() && $('#inputNewKFNickName').val() && $('#inputNewKFN').val()) {
        $.ajax({
            method: 'post',
            url: '/wechat/add_kf',
            data: {
                _token:LA.token,
                kf_account: $('#inputNewKFA').val(),
                nickname: $('#inputNewKFNickName').val(),
                invite_wx: $('#inputNewKFN').val()
            },
            success: function (result) {
                console.log(result)
                $('#new_kf').modal('hide')
                if(result.errcode === 0) {
                    toastr.success('添加成功');
                } else {
                    toastr.warning('添加失败');
                }
                $.pjax.reload('#pjax-container');
            }
        });
    } else {
        toastr.warning('客服账号、客服昵称、微信号不能为空')
    }
})
EOT;
    }
    public function render()
    {
        Admin::script($this->script());

        return view('admin.tools.add_kf');
    }
}