<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class WechatUser extends Model
{
    //
    public $timestamps = false;

    protected $guarded = [];

    public function getTagidListAttribute($value)
    {
        return explode(',', $value);
    }

    public function setTagidListAttribute($value)
    {
        $this->attributes['tagid_list'] = is_array($value) ? implode(',', $value) : $value;
    }
}
