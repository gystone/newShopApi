<?php

namespace App\Http\Controllers\Admin\Wechat;

use EasyWeChat\OfficialAccount\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MenuController extends Controller
{
    private $menu;

    public function __construct(Application $application)
    {
        auth()->shouldUse('api_admin');
        $this->menu = $application->menu;
    }

    public function sync()
    {
        return $this->menu->list();
    }
}
