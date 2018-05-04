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
                    'avatar' => 'required|mimes:jpeg,bmp,png,gif',
                    'password' => 'required|confirmed',
                    'role_id' => 'required',
                    'permission_id' => 'required|exists:admin_permissions,id',
                ];
                break;
            case 'PATCH':
                return [
                    'password' => 'min:6|confirmed',
                    'role_id' => 'exists:admin_roles,id',
                    'permission_id' => 'exists:admin_permissions,id',
                ];
                break;
        }

    }
}
