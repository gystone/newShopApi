<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\AddCartRequest;
use App\Models\CartItem;
use App\Models\ProductSku;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    use ApiResponse;
    protected $user;

    public function __construct()
    {
        auth()->shouldUse('api');

        $this->user = auth('api')->user();
    }

    public function index()
    {
       $cart_list =  $this->user->cartItems()->with(['productSku.product'])->get();

        return $this->success($cart_list,'购物车列表');
    }


    //添加购物车
    public function add(AddCartRequest $request)
    {
        $user = $this->user;
        $sku_id = $request->sku_id;
        $amount = $request->amount;

        // 从数据库中查询该商品是否已经在购物车中
        if ($cart = $user->cartItems()->where('product_sku_id', $sku_id)->first()) {
            // 如果存在则直接叠加商品数量
            $cart->update([
                'amount' => $cart->amount + $amount,
            ]);
        } else {
            // 否则创建一个新的购物车记录
            $cart = new CartItem(['amount' => $amount]);
            $cart->user()->associate($user);
            $cart->productSku()->associate($sku_id);
            $cart->save();
        }

        return $this->success([],'添加成功');
    }

    //从购物车中移除商品
    public function remove(ProductSku $productSku)
    {

        $re = $this->user->cartItems()->where('product_sku_id',$productSku->id)->delete();
        if($re){
            return $this->success([],'删除成功');
        }else{
            return $this->failed('删除失败');
        }

    }


}
