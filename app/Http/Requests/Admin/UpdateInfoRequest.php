<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInfoRequest extends FormRequest
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
        $user_id = auth('api_admin')->user()->id;
        return [
            'id' => 'required|exists:admin_users,id|in:'.$user_id,
            'password' => 'min:6|confirmed',
        ];
    }
}
