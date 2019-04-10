<?php
/**
 * 订单相关业务逻辑
 * Author: 赵振 <270281156@qq.com>
 * Date: 2019/4/9 下午2:59
 */

namespace App\Service;


use App\Exceptions\ApiException;
use App\Jobs\CloseOrder;
use App\Models\CouponCode;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\User;
use App\Models\UserAddress;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class OrderService
{

    //提交订单
    public function store(User $user, UserAddress $address, $remark, $items, CouponCode $couponCode = null)
    {
        // 如果传入了优惠券，则先检查是否可用
        if ($couponCode) {
            $couponCode->checkAvailable($user);
        }

        // 开启一个数据库事务
        $order = \DB::transaction(function () use ($user, $address, $remark, $items, $couponCode) {
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
                'remark' => $remark,
                'total_amount' => 0,
            ]);
            // 订单关联到当前用户
            $order->user()->associate($user);
            // 写入数据库
            $order->save();

            $totalAmount = 0;

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

            //使用优惠券逻辑
            if ($couponCode) {
                // 总金额,检查是否符合优惠券规则
                $couponCode->checkAvailable($user,$totalAmount);
                // 把订单金额修改为优惠后的金额
                $totalAmount = $couponCode->getAdjustedPrice($totalAmount);
                // 将订单与优惠券关联
                $order->couponCode()->associate($couponCode);
                // 增加优惠券的用量，需判断返回值
                if ($couponCode->changeUsed() <= 0){
                    throw new ApiException('该优惠券已被兑完',403);
                }

            }

            // 更新订单总金额
            $order->update(['total_amount' => $totalAmount]);

            // 将下单的商品从购物车中移除
            $skuIds = collect($items)->pluck('sku_id');
            app(CartService::class)->remove($skuIds);

            return $order;
        });

        //延迟关闭未付款订单
//        dispatch(new CloseOrder($order, config('app.order_ttl')));

        return $order;
    }


    //退款逻辑
    public function refundOrder($order)
    {
        //生成退款订单号
        $refundNo = Order::getAvailableRefundNo();

        // 判断该订单的支付方式
        switch ($order->payment_method) {
            case 'wechat':
                // 微信的先留空
                // todo
                break;
            case 'alipay':
                // 支付宝先留空
                // todo
                break;
            case '线下支付':
                // 将订单的退款状态标记为退款成功并保存退款订单号
                $order->update([
                    'refund_no' => $refundNo,
                    'refund_status' => Order::REFUND_STATUS_SUCCESS,
                ]);
                break;
            default:
                throw new ApiException('未知订单支付方式：' . $order->payment_method);
                break;
        }
    }
}