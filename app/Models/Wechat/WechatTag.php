<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class WechatTag extends Model
{
    protected $fillable = [
        'id', 'name', 'count'
    ];
    public $timestamps = false;

    public function tag_users()
    {
        return $this->belongsToMany(WechatUser::class, 'wechat_tag_users', 'tag_id', 'openid');
    }
}
