<?php

namespace App\Http\Requests\Wechat;

use Illuminate\Foundation\Http\FormRequest;

class MenuRequest extends FormRequest
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
            'body.buttons' => 'required|array|max:3',
//            'body.buttons.*.sub_button' => 'required_without:body.buttons.*.type|array|max:5',
//            'body.buttons.*.name' => 'required|max:16',
//            'body.buttons.*.type' => 'required_without:body.buttons.*.sub_button|in:view,click,miniprogram',
//            'body.buttons.*.key' => 'required_if:body.buttons.*.type,click|string|max:128',
//            'body.buttons.*.url' => 'required_if:body.buttons.*.type,miniprogram,view|url|max:1024',
//            'body.buttons.*.appid' => 'required_if:body.buttons.*.type,miniprogram',
//            'body.buttons.*.pagepath' => 'required_if:body.buttons.*.type,miniprogram',
//            'body.buttons.*.sub_button.*.name' => 'required|max:60',
//            'body.buttons.*.sub_button.*.type' => 'required|in:view,click,miniprogram',
//            'body.buttons.*.sub_button.*.key' => 'required_if:body.buttons.*.sub_button.*.type,click|string|max:128',
//            'body.buttons.*.sub_button.*.url' => 'required_if:body.buttons.*.sub_button.*.type,miniprogram,view|url|max:1024',
//            'body.buttons.*.sub_button.*.appid' => 'required_if:body.buttons.*.sub_button.*.type,miniprogram',
//            'body.buttons.*.sub_button.*.pagepath' => 'required_if:body.buttons.*.sub_button.*.type,miniprogram',
        ];
    }

    public function messages()
    {
        return [
            'body.buttons.array' => '非法访问',
            'body.buttons.max' => '一级菜单最多3个',
            'body.buttons.required' => '请设置菜单项',
            'body.buttons.*.sub_button.required_without' => '请设置子菜单项',
            'body.buttons.*.sub_button.array' => '非法访问',
            'body.buttons.*.sub_button.max' => '二级菜单最多5个',
            'body.buttons.*.name.required' => '请填写菜单标题',
            'body.buttons.*.name.max' => '一级菜单标题最长不能超过16个字符',
            'body.buttons.*.type.required_without' => '请选择菜单类型',
            'body.buttons.*.type.in' => '菜单类型不合法',
            'body.buttons.*.key.required_if' => '请输入菜单KEY值',
            'body.buttons.*.key.string' => '菜单KEY值为长度不超过128的字符串',
            'body.buttons.*.key.max' => '菜单KEY值为长度不超过128的字符串',
            'body.buttons.*.url.required_if' => '请输入跳转链接',
            'body.buttons.*.url.url' => '跳转链接为长度不超过1024的url',
            'body.buttons.*.url.max' => '跳转链接为长度不超过1024的url',
            'body.buttons.*.appid.required_if' => '请输入小程序的appid',
            'body.buttons.*.pagepath.required_if' => '请输入小程序的页面路径',
            'body.buttons.*.sub_button.*.name.required' => '请填写子菜单标题',
            'body.buttons.*.sub_button.*.name.max' => '子菜单标题最长不能超过60个字符',
            'body.buttons.*.sub_button.*.type.required' => '请选择子菜单类型',
            'body.buttons.*.sub_button.*.type.in' => '子菜单类型不合法',
            'body.buttons.*.sub_button.*.key.required_if' => '请输入子菜单KEY值',
            'body.buttons.*.sub_button.*.key.string' => '子菜单KEY值为长度不超过128的字符串',
            'body.buttons.*.sub_button.*.key.max' => '子菜单KEY值为长度不超过128的字符串',
            'body.buttons.*.sub_button.*.url.required_if' => '请输入跳转链接',
            'body.buttons.*.sub_button.*.url.url' => '跳转链接为长度不超过1024的url',
            'body.buttons.*.sub_button.*.url.max' => '跳转链接为长度不超过1024的url',
            'body.buttons.*.sub_button.*.appid.required_if' => '请输入小程序的appid',
            'body.buttons.*.sub_button.*.pagepath.required_if' => '请输入小程序的页面路径',
        ];
    }
}
