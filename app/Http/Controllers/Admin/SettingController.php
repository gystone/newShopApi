<?php

namespace App\Http\Controllers\Admin;

use App\Auth\Permission;
use App\Http\Controllers\ApiController;
use App\Models\Image;
use App\Service\Vote\VoteStageService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class SettingController extends BaseController
{
    public function setWechat()
    {
        $appid = \request('appid');
        $secret = \request('secret');
        $token = \request('token');
        $key = \request('key');

        $data = [
            'WECHAT_OFFICIAL_ACCOUNT_APPID' => $appid ?? config('wechat.official_account.default.app_id'),
            'WECHAT_OFFICIAL_ACCOUNT_SECRET' => $secret ?? config('wechat.official_account.default.secret'),
            'WECHAT_OFFICIAL_ACCOUNT_TOKEN' => $token ?? config('wechat.official_account.default.token'),
            'WECHAT_OFFICIAL_ACCOUNT_AES_KEY' => $key ?? config('wechat.official_account.default.aes_key'),
        ];

        modify_env($data);
        Cache::forget('setting_logo');
        return $this->message('设置成功');
    }

    public function getWechat()
    {
        $data = [
            'appid' => config('wechat.official_account.default.app_id'),
            'secret' => config('wechat.official_account.default.secret'),
            'token' => config('wechat.official_account.default.token'),
            'key' => config('wechat.official_account.default.aes_key'),
        ];
        return $this->success($data);
    }

    public function setSite()
    {
        $name = \request('name');
        $logo = \request('logo');

        $data = [
            'SITE_NAME' => $name ?? config('site.name'),
            'SITE_LOGO' => $logo ?? config('site.logo'),
        ];

        modify_env($data);

        $url = Image::where('id', config('site.logo'))->value('url');
        Cache::forever('setting_logo',$url);
        $stage = $this->voteStageService->getStageByCurrent();
        if ($stage) {
            Cache::tags('indexList'.$stage['id'])->flush();
        }
        return $this->message('设置成功');
    }

    public function getSite()
    {
        Permission::check('adminSetting_get');
        $data = [
            'name' => config('site.name'),
            'logo' => config('site.logo') ? url(Image::where('id', config('site.logo'))->value('url')) : config('site.logo'),
        ];

        return $this->success($data);
    }
}
