<?php

namespace App\Http\Requests\Wechat;

use Illuminate\Foundation\Http\FormRequest;

class MaterialNewsRequest extends FormRequest
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
//            'content.news_item.*.title' => 'required',
//            'content.news_item.*.digest' => 'max:64',
//            'content.news_item.*.author' => 'max:10',
//            'content.news_item.*.content' => 'required',
//            'content.news_item.*.content_source_url' => 'url',
//            'content.news_item.*.thumb_media_id' => 'required',
//            'content.news_item.*.show_cover_pic' => 'in:0,1',
            'content.news_item' => 'max:8',
        ];
    }

    public function messages()
    {
        return [
            'content.news_item.*.title.required' => '图文标题必填',
//            'content.news_item.*.digest.max' => '图文摘要最长64个字符',
//            'content.news_item.*.author.max' => '图文作者最长10个字符',
//            'content.news_item.*.content.required' => '图文内容必填',
//            'content.news_item.*.content_source_url.url' => '图文原文链接格式非法',
//            'content.news_item.*.thumb_media_id.required' => '图文封面必填',
//            'content.news_item.*.show_cover_pic.in' => '是否覆盖封面值非法',
            'content.news_item.max' => '多图文消息最多8条',
        ];
    }
}
