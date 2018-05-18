<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class WechatReply extends Model
{
    protected $fillable = ['keyword', 'type', 'content', 'is_equal', 'is_open'];

    public function getKeywordsAttribute($value)
    {
        return unserialize($value);
    }

    public function setKeywordsAttribute($value)
    {
        $this->attributes['keywords'] = serialize($value);
    }

    public function getContentsAttribute($value)
    {
        return unserialize($value);
    }

    public function setContentsAttribute($value)
    {
        $this->attributes['content'] = serialize($value);
    }
}
