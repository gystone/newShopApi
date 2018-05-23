<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class BroadcastRecord extends Model
{
    protected $table = 'wechat_broadcast_records';
    protected $fillable = [
        'tos', 'types', 'message', 'is_cron', 'send_time'
    ];
    public $timestamps = false;

    public function getSendTimeAttribute($value)
    {
        return date('Y-m-d H:i', strtotime($value));
    }

    public function setSendTimeAttribute($value)
    {
        $this->attributes['send_time'] = is_string($value) ? date('Y-m-d H:i', strtotime($value)) : $value;
    }

    public function setToAttribute($value)
    {
        $this->attributes['to'] = serialize($value);
    }

    public function getToAttribute($value)
    {
        return unserialize($value);
    }
}
