<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class WechatMaterial extends Model
{
    protected $table = 'wechat_materials';

    protected $fillable = [
        'media_id', 'type', 'content'
    ];

    public function getContentAttribute($value)
    {
        return unserialize($value);
    }

    public function setContentAttribute($value)
    {
        $this->attributes['content'] = serialize($value);
    }
}
