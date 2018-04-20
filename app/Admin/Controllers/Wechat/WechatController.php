<?php

namespace App\Admin\Controllers\Wechat;

use App\Http\Controllers\Controller;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WechatController extends Controller
{
    public function index()
    {
        Permission::check('wechat');
        return Admin::content(function (Content $content) {
            $content->header('微信公众号配置');
            $content->body($this->form());
        });
    }

    public function form()
    {
        $form = new Form();
        $form->action('wechat');

        $form->text('app_id', 'AppId')->default(env('WECHAT_OFFICIAL_ACCOUNT_APPID'));
        $form->text('secret', 'Secret')->default(env('WECHAT_OFFICIAL_ACCOUNT_SECRET'));
        $form->text('token', 'Token')->default(env('WECHAT_OFFICIAL_ACCOUNT_TOKEN'));
        $form->text('aes_key', 'AES_Key')->default(env('WECHAT_OFFICIAL_ACCOUNT_AES_KEY'));
        $form->text('id', '公众号原始ID')->default(env('WECHAT_OFFICIAL_ACCOUNT_ID'));

        return new Box('配置项', $form);
    }

    public function store(Request $request)
    {
        Permission::check('wechat');

        $app_id = $request->input('app_id');
        $secret = $request->input('secret');
        $token = $request->input('token');
        $aes_key = $request->input('aes_key');
        $id = $request->input('id');

        $data = [
            'WECHAT_OFFICIAL_ACCOUNT_APPID' => $app_id,
            'WECHAT_OFFICIAL_ACCOUNT_SECRET' => $secret,
            'WECHAT_OFFICIAL_ACCOUNT_TOKEN' => $token,
            'WECHAT_OFFICIAL_ACCOUNT_AES_KEY' => $aes_key,
            'WECHAT_OFFICIAL_ACCOUNT_ID' => $id,
        ];

        // 写入.env
        Log::info(modify_env($data));

        admin_toastr('更新成功', 'success');
    }
}
