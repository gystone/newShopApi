<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class WechatReply extends Model
{
    protected $fillable = ['rule_name', 'keywords','contents', 'is_reply_all', 'is_open'];

    public function getKeywordsAttribute($value)
    {
        return unserialize($value);
    }

    public function setKeywordsAttribute($value)
    {
        foreach ($value as $k => &$v) {
            if (is_string($v)) {
                $v = json_decode($v, true);
            }
        }
        $this->attributes['keywords'] = serialize($value);
    }

    public function getContentsAttribute($value)
    {
        return unserialize($value);
    }

    public function setContentsAttribute($value)
    {
        foreach ($value as $k => &$v) {
            if (is_string($v)) {
                $v = json_decode($v, true);
            }
        }
        $this->attributes['contents'] = serialize($value);
    }
}
