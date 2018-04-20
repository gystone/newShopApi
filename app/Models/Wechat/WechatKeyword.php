<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class WechatKeyword extends Model
{
    protected $table='wechat_keywords';
    public $timestamps = false;

    public function text()
    {
        return $this->belongsTo(WechatText::class, 'text_id', 'id');
    }

    public function news()
    {
        return $this->belongsTo(WechatNews::class, 'news_id', 'id');
    }
}
