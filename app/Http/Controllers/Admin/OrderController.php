<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ApiException;
use App\Models\Order;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends BaseController
{

    public function index()
    {
        $orders = Order::with('user')->orderBy('id', 'desc')->get();

        return $this->success($orders, '订单列表');
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'items.productSku']);

        return $this->success($order, '订单详情');
    }

    //后台手动设为已支付
    public function pay(Order $order)
    {
        // 判断当前订单是否已支付
        if ($order->paid_at) {
            return $this->failed('该订单已支付');
        }
        $re = $order->update([
            'paid_at' => Carbon::now(),
            'payment_method' => '线下支付',
            'payment_no' => 0,
        ]);

        if ($re) {
            return $this->success([], '设置成功');
        } else {
            return $this->failed('设置失败');
        }


    }


    //订单发货
    public function ship(Order $order, Request $request)
    {
        // 判断当前订单是否已支付
        if (!$order->paid_at) {
            return $this->failed('该订单未付款');
        }
        // 判断当前订单发货状态是否为未发货
        if ($order->ship_status !== Order::SHIP_STATUS_PENDING) {
            return $this->failed('该订单已发货');
        }
        $data = $this->validate($request, [
            'express_company' => ['nullable'],
            'express_no' => ['nullable'],
        ], [], [
            'express_company' => '物流公司',
            'express_no' => '物流单号',
        ]);

        // 将订单发货状态改为已发货，并存入物流信息
        $re = $order->update([
            'ship_status' => Order::SHIP_STATUS_DELIVERED,
            'ship_data' => [
                'express_company' => $request->express_company ?? '',
                'express_no' => $request->express_no ?? '',
            ],
        ]);

        if ($re) {
            return $this->success([], '发货成功');
        } else {
            return $this->failed('发货失败');
        }


    }


}
