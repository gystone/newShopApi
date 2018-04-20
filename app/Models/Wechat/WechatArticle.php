<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class WechatArticle extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'media_id', 'url'
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $app = app('wechat.official_account');
            Log::info($app->material->delete($model->media_id));
        });
    }

    public function img()
    {
        return $this->belongsTo(WechatImage::class, 'thumb_media_id', 'media_id');
    }
}
