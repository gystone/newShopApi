<?php

namespace App\Http\Resources\Wechat;

use App\Models\Wechat\WechatTag;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\DB;

class User extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $tag_list = [];
        $user_tags = DB::table('wechat_tag_users')->where('openid', $this->openid)->get();
        foreach ($user_tags as $tag) {
            $tag_list[] = WechatTag::select('id', 'name')->where('id', $tag->tag_id)->first();
        }
        return [
            'id' => $this->id,
            'openid' => $this->openid,
            'nickname' => $this->nickname,
            'sex' => $this->sex === 1 ? '男' : '女',
            'headimgurl' => $this->headimgurl,
            'city' => $this->city,
            'province' => $this->province,
            'country' => $this->country,
            'remark' => $this->remark,
            'tag_list' => $tag_list,
            'subscribe_time' => $this->subscribe_time,
            'is_blacklist' => $this->is_blacklist,
        ];
    }
}
