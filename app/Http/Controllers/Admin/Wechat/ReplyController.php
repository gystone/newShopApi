<?php

namespace App\Http\Controllers\Admin\Wechat;

use App\Http\Controllers\ApiController;
use App\Models\Wechat\WechatReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReplyController extends ApiController
{
    public function __construct()
    {
        auth()->shouldUse('api_admin');
    }

    public function store(Request $request)
    {
        $res = WechatReply::create([
            'rule_name' => $request->rule_name,
            'keywords' => $request->keywords,
            'contents' => $request->contents,
            'is_reply_all' => $request->is_reply_all,
            'is_open' => $request->is_open
        ]);

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

    public function update(WechatReply $reply, Request $request)
    {
        $attributes = $request->only('keywords','contents', 'is_reply_all', 'is_open');

        $reply->keywords = $request->keywords ?? $reply->keywords;
        $reply->contents = $request->contents ?? $reply->contents;
        $reply->is_reply_all = $request->is_reply_all ?? $reply->is_reply_all;
        $reply->is_open = $request->is_open ?? $reply->is_open;
        $res = $reply->save();

        if ($res) {
            return $this->success($reply);
        } else {
            $this->failed('修改失败，请稍候重试');
        }
    }

    public function delete(WechatReply $reply)
    {
        $res = $reply->delete();

        if ($res) {
            return $this->message('删除成功');
        } else {
            return $this->failed('删除失败，请稍候重试');
        }
    }
}
