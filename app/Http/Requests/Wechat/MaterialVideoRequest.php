<?php

namespace App\Http\Requests\Wechat;

use Illuminate\Foundation\Http\FormRequest;

class MaterialVideoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'video' => 'required|mimetypes:video/*',
            'title' => 'required|string|max:21',
            'description' => 'required|string|max:120'
        ];
    }

    public function messages()
    {
        return [
            'video.required' => '请上传视频',
            'video.mimetypes' => '视频格式有误',
            'title.required' => '请输入标题',
            'title.string' => '标题为长度不超过21的字符串',
            'title.max' => '标题为长度不超过21的字符串',
            'description.required' => '请输入标题',
            'description.string' => '描述为长度不超过120的字符串',
            'description.max' => '描述为长度不超过120的字符串',
        ];
    }
}
