<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Http\Requests\Api\OrderRequest;
use App\Jobs\CloseOrder;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\UserAddress;
use App\Service\CartService;
use App\Service\OrderService;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrdersController extends Controller
{
    use ApiResponse;
    protected $user;

    public function __construct()
    {
        auth()->shouldUse('api');
        $this->user = auth('api')->user();

    }

    //提交订单
    public function store(OrderRequest $request, OrderService $orderService)
    {
        $user = $this->user;
        $address = UserAddress::find($request->address_id);
        
        $order = $orderService->store($user, $address, $request->remark, $request->items);

        return $this->success($order, '提交成功');
    }

    public function index()
    {
        $orders = Order::query()
            ->with(['items.product', 'items.productSku'])
            ->where('user_id', $this->user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success($orders, '订单列表');
    }

    public function show(Order $order)
    {

        $this->authorize('own', $order);

        $order->load(['items.product', 'items.productSku']);

        return $this->success($order, '订单详情');

    }


}
