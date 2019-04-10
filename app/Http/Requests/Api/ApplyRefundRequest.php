<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\RequestBase;
use Illuminate\Foundation\Http\FormRequest;

class ApplyRefundRequest extends RequestBase
{

    public function rules()
    {
        return [
            'reason' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'reason' => '原因',
        ];
    }
}
