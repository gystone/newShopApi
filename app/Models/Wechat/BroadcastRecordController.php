<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class BroadcastRecordController extends Model
{
    protected $fillable = [
        'to', 'types', 'message', 'is_cron', 'send_time'
    ];
    public $timestamps = false;

    public function getSendTimeAttribute($value)
    {
        return date('Y-m-d H:i', $value);
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
