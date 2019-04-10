<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\RequestBase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendReviewRequest extends RequestBase
{

    public function rules()
    {
        return [
            'reviews' => ['required', 'array'],
            'reviews.*.id' => [
                'required',
                Rule::exists('order_items', 'id')->where('order_id', $this->route('order')->id)
            ],
            'reviews.*.rating' => ['required', 'integer', 'between:1,5'],
            'reviews.*.review' => ['required'],
        ];
    }

    public function attributes()
    {
        return [
            'reviews.*.id' => '订单项ID',
            'reviews.*.rating' => '评分',
            'reviews.*.review' => '评价',
        ];
    }
}
