<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class WechatMaterial extends Model
{
    protected $table = 'wechat_materials';
    public $timestamps = false;

    protected $fillable = [
        'media_id', 'content'
    ];
}
