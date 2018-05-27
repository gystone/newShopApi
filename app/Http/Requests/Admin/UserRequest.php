<?php

namespace App\Http\Requests\Admin;

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
        switch ($this->method()) {
            case 'POST':
                return [
                    'username' => 'required',
                    'name' => 'required',
                   // // 'avatar' => 'required',
                    'password' => 'required|confirmed',
                    'role_id' => 'required|exists:admin_roles,id',
                ];
                break;
            case 'PATCH':
                return [
                    'password' => 'min:6|confirmed',
                    'role_id' => 'exists:admin_roles,id',
                ];
                break;
        }
    }

    public function messages()
    {
        return [
            'username.required' => '请输入用户名',
            'name.required' => '请输入姓名',
            // 'avatar.required' => '头像不能为空',
            'password.required' => '请输入密码',
            'password.confirmed' => '密码不匹配',
            'role_id.required' => '请分配角色',
            'role_id.exists' => '角色非法'
        ];
    }
}
