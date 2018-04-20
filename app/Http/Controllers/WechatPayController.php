<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Order;
use App\Models\Patient;
use App\Models\PayLog;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WechatPayController extends Controller
{
    protected $app;

    public function __construct()
    {
        $this->app = app('wechat.payment');
    }

    public function unifiedOrder(Request $request)
    {

        $result = $this->app->order->unify([
            'body' => '微信支付',
            'out_trade_no' => '商户订单号',
            'total_fee' => '支付金额',
            'notify_url' => url('wechat/pay/notify'),
            'trade_type' => '交易类型', // JSAPI|NATIVE|APP
            'openid' => 'OPENID',
        ]);

        if (isset($result['result_code']) && $result['result_code'] == 'SUCCESS') {
            //记录Prepay_id
            PayLog::updateOrCreate(
                [
                    'openid' => 'OPENID',
                    'order_no' => '订单编号',
                    'pay_ls' => '商户订单号'
                ],
                [
                    'openid' => 'OPENID',
                    'pay_type' => 'wechat',
                    'order_no' => '订单编号',
                    'pay_ls' => '商户订单号',
                    'fee' => '支付金额',
                    'pay_status' => 0,
                    'type' => 0,
                    'fk_type' => 'wechat'
                ]);

            $data = $this->app->jssdk->bridgeConfig($result['prepay_id']);
            return $data;
        }
    }

    public function notify()
    {
        $app = $this->app;
        $response = $this->app->handlePaidNotify(function($message, $fail) use ($app){

            $pay_log = PayLog::where('pay_ls', $message['out_trade_no'])->first();
            $out_trade_no = $message['out_trade_no'];
            $order = '订单信息';

            // 判断支付状态
            $case = !$pay_log || $pay_log->pay_status || $pay_log->fee != $message['total_fee'] || !$order;
            if ($case) {
                Log::info('已支付或订单不存在');
                return true;
            }

            if ($message['return_code'] === 'SUCCESS') {
                if (array_get($message, 'result_code') === 'SUCCESS') {
                    // 修改订单状态、支付状态等
                    Log::info('支付成功'.$out_trade_no);
                    $pay_log->pay_status = 1;
                    $pay_log->transaction_id = $message['transaction_id'];
                    $order->save();
                    $pay_log->save();
                } elseif (array_get($message, 'result_code') === 'FAIL') {
                    Log::info('支付失败'.$out_trade_no);
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }

            return true;
        });

        return $response;
    }

}
