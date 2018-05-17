<?php

namespace App\Http\Controllers\Admin\Wechat;

use App\Http\Controllers\ApiController;
use App\Models\Wechat\WechatReply;
use Illuminate\Http\Request;

class ReplyController extends ApiController
{
    public function __construct()
    {
        auth()->shouldUse('api_admin');
    }

    public function store(Request $request)
    {
        $attributes = $request->only('keyword', 'type', 'content', 'is_equal', 'is_open');

        $res = WechatReply::create($attributes);

        if ($res) {
            return $this->success($res);
        } else {
            return $this->failed('设置失败，请稍候重试');
        }
    }

    public function list()
    {
        return $this->success(WechatReply::latest()->get());
    }
}
