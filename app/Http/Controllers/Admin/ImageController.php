<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ApiException;
use App\Http\Requests\ImageRequest;
use App\Models\Image;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ImageController extends Controller
{
    use ApiResponse;

    /**
     * 后台图片上传
     * @param ImageRequest $request
     * @return mixed
     * @throws ApiException
     */
    public function store(ImageRequest $request)
    {
        $ret_arr = [
            'type' => 1,
            'url' => null,
        ];
        if ($request->has('type') && !empty($request->type)) {
            $ret_arr['url'] = $request->type;
        }
        $time = date('Y/m/d');
        try {
            $path = $request->file('image')->store(config('common.upload.url') . $time, config('common.upload.disks'));
            $ret_arr['url'] = '/uploads/'.$path;
            $image = Image::create($ret_arr);
            return $this->success(new \App\Http\Resources\Image($image));
        } catch (\Exception $exception) {
            if(config('common.upload.disks') == 'qiniu'){
                throw new ApiException('上传失败，请检查七牛配置项是否正确');
            }
            throw new ApiException('图片上传失败，请稍后重试');
        }
    }
}
