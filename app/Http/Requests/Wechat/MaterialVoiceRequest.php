<?php

namespace App\Http\Requests\Wechat;

use Illuminate\Foundation\Http\FormRequest;

class MaterialVoiceRequest extends FormRequest
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
            'voice' => 'required|mimes:mp3,wma,wav,amr|max:30720'
        ];
    }

    public function messages()
    {
        return [
            'voice.required' => '请上传声音文件',
            'voice.mimes' => '声音格式支持mp3、wma、wav、amr',
            'voice.max' => '文件大小不能超过30M'
        ];
    }
}
