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

    public function setTosAttribute($value)
    {
        $this->attributes['tos'] = serialize($value);
    }

    public function getTosAttribute($value)
    {
        return unserialize($value);
    }
}
