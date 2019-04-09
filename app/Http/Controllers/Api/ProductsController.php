<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductsController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $products = Product::query()->with(['image', 'skus'])->where('on_sale', true)->get();

        return $this->success($products);
    }

    public function show(Product $product)
    {

        if (!$product->on_sale) {
            return $this->failed('商品未上架');
        }
        $product->image;
        $product->skus;

        $favored = false;
        //检查用户是否登录
        if ($user = auth('api')->user()) {
            // 从当前用户已收藏的商品中搜索 id 为当前商品 id 的商品
            $favored = boolval($user->favoriteProducts()->find($product->id));
        }
        $product->favored = $favored;//是否收藏

        return $this->success($product, '商品详情');
    }

    //收藏
    public function favor(Product $product)
    {
        $user = auth('api')->user();

        if ($user->favoriteProducts()->find($product->id)) {
            return $this->failed('商品已收藏');
        }
        $user->favoriteProducts()->attach($product);

        return $this->success('收藏成功');
    }

    //取消收藏
    public function disfavor(Product $product)
    {
        $user = auth('api')->user();
        $user->favoriteProducts()->detach($product);

        return $this->success('取消成功');

    }

    //收藏列表
    public function favorites()
    {
        $user = auth('api')->user();
        $favor_list = $user->favoriteProducts()->get();

        return $this->success($favor_list,'收藏列表');

    }

}
