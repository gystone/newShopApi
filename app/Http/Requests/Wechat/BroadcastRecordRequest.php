<?php

namespace App\Http\Requests\Wechat;

use Illuminate\Foundation\Http\FormRequest;

class BroadcastRecordRequest extends FormRequest
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
            'to' => 'required',
            'message' => 'required',
            'types' => 'required',
            'is_cron' => 'required',
            'send_time' => 'required_if:is_cron,1',
        ];
    }

    public function messages()
    {
        return [
            'to.required' => '请确定发送对象',
            'message.required' => '请确定发送内容',
            'types.required' => '请确定消息类型',
            'is_cron.required' => '请确定是否定时发送',
            'send_time.required_if' => '请确定发送时间',
        ];
    }
}
