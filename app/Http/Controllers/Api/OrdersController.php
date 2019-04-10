<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderReviewed;
use App\Exceptions\ApiException;
use App\Http\Requests\Api\ApplyRefundRequest;
use App\Http\Requests\Api\OrderRequest;
use App\Http\Requests\Api\SendReviewRequest;
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

    //确认收货
    public function received(Order $order)
    {
        $this->authorize('own', $order);

        // 判断订单的发货状态是否为已发货
        if ($order->ship_status !== Order::SHIP_STATUS_DELIVERED) {
            return $this->failed('发货状态不正确');
        }

        // 更新发货状态为已收到
        $re = $order->update(['ship_status' => Order::SHIP_STATUS_RECEIVED]);

        if ($re) {
            return $this->success([], '确认成功');
        } else {
            return $this->failed('确认失败');
        }

    }

    //订单评价
    public function sendReview(Order $order, SendReviewRequest $request)
    {
        $this->authorize('own', $order);

        if (!$order->paid_at) {
            return $this->failed('该订单未支付，不可评价');
        }
        if ($order->reviewed) {
            return $this->failed('该订单已评价，不可重复提交');
        }

        $reviews = $request->reviews;
        // 开启事务
        \DB::beginTransaction();
        try {
            // 遍历用户提交的数据
            foreach ($reviews as $review) {
                $orderItem = $order->items()->find($review['id']);
                // 保存评分和评价
                $orderItem->update([
                    'rating' => $review['rating'],
                    'review' => $review['review'],
                    'reviewed_at' => Carbon::now(),
                ]);
            }
            // 将订单标记为已评价
            $order->update(['reviewed' => true]);

            //发送事件计算商品总评价和平均分数事件
//            event(new OrderReviewed($order));

            \DB::commit();

            return $this->success([], '评价成功');
        } catch (\Exception $exception) {
            \DB::rollBack();
            return $this->failed('评价失败');
        }
    }

    //申请退款
    public function applyRefund(Order $order, ApplyRefundRequest $request)
    {
        $this->authorize('own', $order);
        // 判断订单是否已付款
        if (!$order->paid_at) {
            return $this->failed('该订单未支付，不可退款');
        }

        // 判断订单退款状态是否正确
        if ($order->refund_status !== Order::REFUND_STATUS_PENDING) {
            return $this->failed('该订单已经申请过退款，请勿重复申请');
        }

        // 将用户输入的退款理由放到订单的 extra 字段中
        $extra = $order->extra ?: [];
        $extra['refund_reason'] = $request->reason;
        // 将订单退款状态改为已申请退款
        $re = $order->update([
            'refund_status' => Order::REFUND_STATUS_APPLIED,
            'extra' => $extra,
        ]);

        if ($re) {
            return $this->success([], '申请退款成功');
        } else {
            return $this->failed('申请退款失败');
        }

    }


}
