<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Order;
use App\Models\Patient;
use App\Models\PayLog;
use App\Services\Wechat\WechatPayService;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WechatPayController extends Controller
{
    /**
     * 支付回调
     * @return mixed
     */
    public function notify()
    {
        return (new WechatPayService())->notify();
    }

}
