<?php

namespace App\Http\Controllers\Api;

use App\Models\CouponCode;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CouponCodesController extends Controller
{
    use ApiResponse;
    protected $user;

    public function __construct()
    {
        auth()->shouldUse('api');
        $this->user = auth('api')->user();
    }


    //检查优惠券
    public function checkCouponCode(Request $request)
    {
        $code = $request->code;

        // 判断优惠券是否存在
        $record = CouponCode::where('code', $code)->firstOrFail();

        //检查
        $record->checkAvailable($this->user);

        return $this->success($record);

    }


}
