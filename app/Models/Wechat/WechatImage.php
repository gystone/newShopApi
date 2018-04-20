<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class WechatImage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'media_id', 'url'
    ];
}
