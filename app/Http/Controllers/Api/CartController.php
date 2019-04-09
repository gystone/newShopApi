<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\AddCartRequest;
use App\Models\CartItem;
use App\Models\ProductSku;
use App\Service\CartService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    use ApiResponse;
    protected $user;
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        auth()->shouldUse('api');

        $this->user = auth('api')->user();
        $this->cartService = $cartService;
    }

    public function index()
    {
        $cart_list = $this->cartService->get();

        return $this->success($cart_list, '购物车列表');
    }


    //添加购物车
    public function add(AddCartRequest $request)
    {
        $sku_id = $request->sku_id;
        $amount = $request->amount;

        $this->cartService->add($sku_id, $amount);


        return $this->success([], '添加成功');
    }

    //从购物车中移除商品
    public function remove(ProductSku $productSku)
    {
        $re = $this->cartService->remove($productSku->id);

        if ($re) {
            return $this->success([], '删除成功');
        } else {
            return $this->failed('删除失败');
        }

    }


}
