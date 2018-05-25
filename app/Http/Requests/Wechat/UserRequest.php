<?php

namespace App\Http\Requests\Wechat;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'remark' => 'between:1,30',
            'openids' => 'array|exists:wechat_users,openid',
        ];
    }

    public function messages()
    {
        return [
            'remark.between' => '备注名为1-30之间字符串',
            'openids.array' => '非法访问',
            'openids.exists' => '非法访问',
        ];
    }
}
