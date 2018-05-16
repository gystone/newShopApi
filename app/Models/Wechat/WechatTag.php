<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class WechatTag extends Model
{
    protected $fillable = [
        'id', 'name', 'count'
    ];
    public $timestamps = false;
}
