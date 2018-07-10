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
            $ret_arr['url'] = $path;
            $image = Image::query();
            $image = $image->fill($ret_arr);
            $image = $image->save();
            return $this->success(new \App\Http\Resources\Image($image));
        } catch (\Exception $exception) {
            throw new ApiException();
        }
    }
}
