<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductsController extends BaseController
{
    public function index()
    {
        $list = Product::with('skus')->orderBy('id', 'desc')->get();

        return $this->success($list, '商品列表');
    }

    public function store(ProductRequest $request)
    {
        \DB::beginTransaction();
        try {
            $price = collect($request->skus)->min('price') ?: 0;
            $skus = $request->skus;

            $product = Product::create([
                'title' => $request->title,
                'description' => $request->description,
                'image_id' => $request->image_id,
                'on_sale' => $request->on_sale,
                'price' => $price,
            ]);
            foreach ($skus as $sku) {
                $product->skus()->create($sku);
            }

            \DB::commit();
            return $this->success([], '添加成功');

        } catch (\Exception $exception) {
            \DB::rollBack();
            return $this->failed('添加失败');
        }

    }

    public function show(Product $product)
    {
        return $this->success($product->with('skus')->first());
    }


    public function update(Product $product, ProductRequest $request)
    {
        \DB::beginTransaction();
        try {

            $price = collect($request->skus)->min('price') ?: 0;
            //更新product
            $product->title = $request->title;
            $product->description = $request->description;
            $product->image_id = $request->image_id;
            $product->on_sale = $request->on_sale;
            $product->price = $price;
            $product->save();

            //更新skus
            $skus = collect($request->skus)->keyBy('id');
            foreach ($product->skus as $v) {
                $new_sku = $skus->where('id', $v->id)->first();
                if ($new_sku) {
                    $v->title = $new_sku['title'];
                    $v->description = $new_sku['description'];
                    $v->price = $new_sku['price'];
                    $v->stock = $new_sku['stock'];
                    $v->save();
                    //删除使用过的sku
                    $skus->forget($v->id);
                } else {
                    $v->delete();
                }
            }
            foreach ($skus as $v) {
                $product->skus()->create($v);
            }
            \DB::commit();
            return $this->success([], '更新成功');

        } catch (\Exception $exception) {
            \DB::rollBack();
            return $this->failed('更新失败');
        }
    }

    public function destroy(Product $product)
    {
        \DB::beginTransaction();
        try {
            $product->skus()->delete();
            $product->delete();
            \DB::commit();
            return $this->success([], '删除成功');
        } catch (\Exception $exception) {
            \DB::rollBack();
            return $this->failed('删除失败');

        }

    }



}
