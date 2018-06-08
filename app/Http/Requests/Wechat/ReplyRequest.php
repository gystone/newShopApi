<?php

namespace App\Http\Requests\Wechat;

use Illuminate\Foundation\Http\FormRequest;

class ReplyRequest extends FormRequest
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
            case 'GET':
                return [
                    'rule_name' => 'required|exists:wechat_replies,rule_name',
                ];
                break;
            case 'POST':
                return [
                    'rule_name' => 'required|between:2,50',
                    'keywords' => 'required|array',
//                    'keywords.*.match_mode' => 'required|in:contain,equal',
//                    'keywords.*.content' => 'required|between:1,50',
                    'contents' => 'required|array',
//                    'contents.*.type' => 'required|in:text,image,voice,video,news',
//                    'contents.*.content' => 'required|min:1',
                    'is_reply_all' => 'required|in:0,1',
                    'is_open' => 'required|in:0,1',
                ];
                break;
            case 'PATCH':
                return [
                    'rule_name' => 'between:2,50',
                    'keywords' => 'array',
//                    'keywords.*.match_mode' => 'required|in:contain,equal',
//                    'keywords.*.content' => 'required|between:1,50',
                    'contents' => 'array',
//                    'contents.*.type' => 'required|in:text,image,voice,video,news',
//                    'contents.*.content' => 'required|min:1',
                    'is_reply_all' => 'in:0,1',
                    'is_open' => 'in:0,1',
                ];
                break;
        }
    }

    public function messages()
    {
        return [
            'rule_name.required' => '请输入规则名称',
            'rule_name.between' => '规则名称应为2-50之间字符串',
            'rule_name.exists' => '规则不存在',
            'keywords.required' => '请输入关键词',
            'keywords.array' => '非法访问',
//            'keywords.*.match_mode.required' => '请选择关键词匹配方式',
//            'keywords.*.match_mode.in' => '非法访问',
//            'keywords.*.content.required' => '请输入关键词内容',
//            'keywords.*.content.between' => '关键词应为1-50之间字符串',
            'contents.required' => '请输入回复内容',
            'contents.array' => '非法访问',
//            'contents.*.type.required' => '请选择回复消息类型',
//            'contents.*.type.in' => '非法访问',
//            'contents.*.content.required' => '请输入回复内容',
//            'contents.*.content.min' => '请输入回复内容',
            'is_reply_all.required' => '请选择是否全部发送',
            'is_reply_all.in' => '非法访问',
            'is_open.required' => '请选择是否开启',
            'is_open.in' => '非法访问',
        ];
    }
}
