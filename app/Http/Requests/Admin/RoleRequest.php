<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
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
                    'name' => 'required|unique:admin_roles,name',
                    'permissions' => 'required|array',
                ];
                break;
            case 'PATCH':
                return [
                    'permissions' => 'array',
                ];
                break;
        }
    }

    public function messages()
    {
        return [
            'name.required' => '请输入角色名称',
            'name.unique' => '角色名称重复',
            'permissions.required' => '请为角色分配权限',
            'permission.array' => '权限类型有误',
        ];
    }
}
