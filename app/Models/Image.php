<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    protected $fillable = ['url', 'type'];


    public function getUrlAttribute($value)
    {
        if(config('common.upload.disks')=='qiniu'){
            return Storage::disk(config('common.upload.disks'))->geturl($value);
        }else{
            return url($value);
        }

    }
}
