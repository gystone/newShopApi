<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class WechatReply extends Model
{
    protected $fillable = ['keyword', 'type', 'content', 'is_equal', 'is_open'];

    public function getContentAttribute($value)
    {
        return unserialize($value);
    }

    public function setContentAttribute($value)
    {
        $this->attributes['content'] = serialize($value);
    }
}
