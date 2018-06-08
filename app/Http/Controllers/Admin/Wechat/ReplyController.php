<?php

namespace App\Http\Controllers\Admin\Wechat;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Wechat\ReplyRequest;
use App\Http\Resources\Wechat\Reply;
use App\Http\Resources\Wechat\ReplyCollection;
use App\Models\Wechat\WechatReply;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ReplyController extends ApiController
{
    public function __construct()
    {
        auth()->shouldUse('api_admin');
    }

    public function store(ReplyRequest $request)
    {
        $attributes = $request->only('rule_name', 'keywords','contents', 'is_reply_all', 'is_open');

        $res = WechatReply::create($attributes);

        if ($res) {
            return $this->success(new Reply($res));
        } else {
            return $this->failed('设置失败，请稍候重试');
        }
    }

    public function list()
    {
        return $this->success(\request('page') ?
            new ReplyCollection(WechatReply::latest()->paginate(\request('limit') ?? 20)) :
            Reply::collection(WechatReply::latest()->get())
        );
    }

    public function getContentByKeyword(ReplyRequest $request)
    {
        return $this->success(new Reply(WechatReply::where('rule_name', $request->rule_name)->first()));
    }

    public function update(WechatReply $reply, ReplyRequest $request)
    {
        $attributes = $request->only('keywords','contents', 'is_reply_all', 'is_open');

        $res = $reply->update($attributes);

        if ($res) {
            return $this->success(new Reply($reply));
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

    public function search()
    {
        $content = \request('search');
        $reply_list = WechatReply::latest()->get();
        $data = [];
        foreach ($reply_list as $k => $v) {
            foreach ($v['keywords'] as $k1 => $v1) {
                if (stripos($v1['content'], $content) !== false) {
                    $data[] = $v;
                }
            }
        }

        // 结果去重
        $res_data = [];
        foreach (array_unique($data) as $item) {
            $res_data[] = json_decode($item, true);
        }
        $res = $this->success(\request('page') ?
            new $this->paginated($res_data, \request('limit') ?? 20) : $res_data);

        return $res;
    }

    private function paginated($data, $num)
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage() - 1;

        $collection = new Collection($data);

        $perPage = $num;

        $currentPageSearchResults = $collection->slice($currentPage * $perPage, $perPage)->all();

        $paginatedSearchResults= new LengthAwarePaginator($currentPageSearchResults, count($collection), $perPage);

        return $paginatedSearchResults;
    }
}
