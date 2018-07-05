<?php

namespace App\Services\Wechat;
use App\Models\PayLog;
use App\Models\Shop\Order;
use Illuminate\Support\Facades\Log;

/**
 * 微信支付业务
 * Class WechatPayService
 * @package App\Services\Wechat
 */
class WechatPayService
{
    protected $app;

    public function __construct()
    {
        $this->app = app('wechat.payment');
    }

    public function unifiedOrder(string $body, string $order_no, $total_fee, $openid)
    {
        $out_trade_no = $order_no.date('YmdHis');
        $result = $this->app->order->unify([
            'body' => $body,
            'out_trade_no' => $out_trade_no,
            'total_fee' => $total_fee,
            'notify_url' => url('wechat/pay/notify'),
            'trade_type' => 'JSAPI', // JSAPI|NATIVE|APP
            'openid' => $openid,
        ]);

        if (isset($result['result_code']) && $result['result_code'] == 'SUCCESS') {
            //记录Prepay_id
            PayLog::updateOrCreate(
                [
                    'openid' => $openid,
                    'order_no' => $order_no,
                    'pay_ls' => $out_trade_no
                ],
                [
                    'openid' => $openid,
                    'pay_type' => 'wechat',
                    'order_no' => $order_no,
                    'pay_ls' => $out_trade_no,
                    'fee' => $total_fee * 100,
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
            $order = Order::where('order_no', $pay_log->order_no)->first();

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
                    $order->pay_status = '1';
                    $order->status = '1';
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