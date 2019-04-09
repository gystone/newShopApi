<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends BaseController
{

    public function index()
    {
        $orders = Order::with('user')->orderBy('id', 'desc')->get();

        return $this->success($orders,'订单列表');
    }

    public function show(Order $order)
    {
        $order->load(['user','items.product','items.productSku']);

        return $this->success($order,'订单详情');
    }



}
