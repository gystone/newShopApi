<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Http\Requests\Api\OrderRequest;
use App\Jobs\CloseOrder;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\UserAddress;
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

    public function store(OrderRequest $request)
    {
        $user = $this->user;
        // 开启一个数据库事务
        $order = \DB::transaction(function () use ($user, $request) {
            $address = UserAddress::find($request->address_id);
            // 更新此地址的最后使用时间
            $address->update(['last_used_at' => Carbon::now()]);
            // 创建一个订单
            $order = new Order([
                'address' => [ // 将地址信息放入订单中
                    'address' => $address->full_address,
                    'zip' => $address->zip,
                    'contact_name' => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark' => $request->input('remark'),
                'total_amount' => 0,
            ]);
            // 订单关联到当前用户
            $order->user()->associate($user);
            // 写入数据库
            $order->save();

            $totalAmount = 0;
            $items = $request->items;
            // 遍历用户提交的 SKU
            foreach ($items as $data) {
                $sku = ProductSku::find($data['sku_id']);
                // 创建一个 OrderItem 并直接与当前订单关联
                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price' => $sku->price,
                ]);
                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();
                $totalAmount += $sku->price * $data['amount'];

                //减库存
                if ($sku->decreaseStock($data['amount']) <= 0) {
                    throw new ApiException($sku->product->title . ' ' . $sku->title . ' 库存不足', 400);
                }

            }

            // 更新订单总金额
            $order->update(['total_amount' => $totalAmount]);

            // 将下单的商品从购物车中移除
            $skuIds = collect($items)->pluck('sku_id');
            $user->cartItems()->whereIn('product_sku_id', $skuIds)->delete();

            return $order;
        });

        //延迟关闭未付款订单
//        $this->dispatch(new CloseOrder($order, config('app.order_ttl')));


        return $this->success($order, '提交成功');

    }
}
