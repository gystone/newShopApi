<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\RequestBase;
use Illuminate\Foundation\Http\FormRequest;

class HandleRefundRequest extends RequestBase
{

    public function rules()
    {
        return [
            'agree'  => ['required', 'boolean'],
            'reason' => ['required_if:agree,false'], // 拒绝退款时需要输入拒绝理由
        ];
    }
}
