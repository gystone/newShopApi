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
        $products = Product::query()->with(['image','skus'])->where('on_sale', true)->get();

        return $this->success($products);
    }

    public function show(Product $product,Request $request)
    {

        if(!$product->on_sale){
            return $this->failed('商品未上架');
        }
        $product->image;
        $product->skus;

        return $this->success($product, '商品详情');

    }

}
