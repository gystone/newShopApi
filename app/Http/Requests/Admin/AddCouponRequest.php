<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\RequestBase;
use App\Models\CouponCode;
use Illuminate\Foundation\Http\FormRequest;

class AddCouponRequest extends RequestBase
{

    public function rules()
    {
        return [
            'name' => 'required',
            'code' => 'nullable|unique:coupon_codes',
            'type' => 'required',
            'value' => 'required',// todo 如果选择了百分比折扣类型，那么折扣范围只能是 1 ~ 99,否则只要大等于 0.01 即可
            'total' => 'required|numeric|min:0',
            'min_amount' => 'required|numeric|min:0',
            'not_before' => 'datetime',
            'not_after' => 'datetime',//todo 结束时间大于开始时间
            'enabled' => 'boolean',

        ];
    }
}
