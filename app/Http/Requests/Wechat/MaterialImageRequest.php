<?php

namespace App\Http\Requests\Wechat;

use Illuminate\Foundation\Http\FormRequest;

class MaterialImageRequest extends FormRequest
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
            'img' => 'required|image',
        ];
    }

    public function messages()
    {
        return [
            'img.required' => '请上传图片',
            'img.image' => '图片格式不对'
        ];
    }
}
