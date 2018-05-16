<?php

namespace App\Http\Controllers\Admin\Wechat;

use App\Http\Controllers\ApiController;
use EasyWeChat\OfficialAccount\Application;

class TagController extends ApiController
{
    private $tag;

    public function __construct(Application $app)
    {
        auth()->shouldUse('api_admin');
        $this->tag = $app->user_tag;
    }

    public function sync()
    {
        $list = $this->tag->list();


    }
}
