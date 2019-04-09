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

}
