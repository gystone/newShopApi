<?php

namespace App\Http\Controllers\Admin\Wechat;

use App\Http\Controllers\ApiController;
use App\Models\Wechat\WechatMenu;
use EasyWeChat\OfficialAccount\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MenuController extends ApiController
{
    private $menu;

    public function __construct(Application $application)
    {
        auth()->shouldUse('api_admin');
        $this->menu = $application->menu;
    }

    public function sync()
    {
//        try {
            $list = $this->menu->list();
            if (isset($list['menu']) && count($list['menu']['button'])) {
                WechatMenu::updateOrCreate([
                    'type' => 'normal'
                ], [
                    'type' => 'normal',
                    'buttons' => $list['menu']['button']
                ]);
            }
            return $this->message('同步成功');
//        } catch (\Exception $exception) {
//            Log::info('menu_sync error:'.$exception->getMessage());
//            return $this->failed('同步失败，请稍候重试');
//        }
    }

    public function list()
    {
        $menu = WechatMenu::where('type', 'normal')->first();

        return $menu ? $menu->buttons : [];
    }
}
