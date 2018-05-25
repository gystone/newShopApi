<?php

namespace App\Http\Requests\Wechat;

use Illuminate\Foundation\Http\FormRequest;

class TagRequest extends FormRequest
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
            'name' => 'between:1,30',
            'openids' => 'array|exists:wechat_users,openid',
            'tagids' => 'array|exists:wechat_tags,id'
        ];
    }

    public function messages()
    {
        return [
            'name.between' => '标签名为30以内的字符串',
            'openids.array' => '非法访问',
            'openids.exists' => '非法访问',
            'tagids.array' => '非法访问',
            'tagids.exists' => '非法访问',
        ];
    }
}
