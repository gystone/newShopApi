<?php

namespace App\Http\Controllers\Admin\Wechat;

use App\Exceptions\ApiException;
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
        try {
            $list = $this->menu->current();
            if (isset($list['menu']) && count($list['menu']['button'])) {
                WechatMenu::updateOrCreate([
                    'type' => 'normal'
                ], [
                    'type' => 'normal',
                    'buttons' => $list['menu']['button']
                ]);
            }
            return $this->message('同步成功');
        } catch (\Exception $exception) {
            Log::info('menu_sync error:'.$exception->getMessage());
            return $this->failed('同步失败，请稍候重试');
        }
    }

    public function list()
    {
        $menu = WechatMenu::where('type', 'normal')->first();

        return $this->success($menu ? $menu->buttons : []);
    }

    // TODO: request加验证
    public function create(Request $request)
    {
        $buttons = $request->buttons;

        $res = $this->menu->create($buttons);

        if ($res['errcode'] === 0) {
            WechatMenu::updateOrCreate([
                'type' => 'normal'
            ], [
                'type' => 'normal',
                'buttons' => $buttons
            ]);
            return $this->message('创建成功');
        } else {
            throw new ApiException('abc', 400);
//            return $this->failed('创建失败，请稍候重试');
        }
    }

    public function delete()
    {
        $res = $this->menu->delete();

        if ($res['errcode'] === 0) {
            WechatMenu::where('type', 'normal')->delete();
            return $this->message('删除成功');
        } else {
            return $this->failed('删除失败，请稍候重试');
        }
    }
}
