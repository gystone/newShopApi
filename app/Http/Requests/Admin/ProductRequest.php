<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\RequestBase;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends RequestBase
{

    public function rules()
    {
        return [
            'title'=>'required',
            'image_id'=>'required|integer',
            'description'=>'required',
            'on_sale'=>'in:1,0',
            'skus.*.title'=>'required',
            'skus.*.description'=>'required',
            'skus.*.price'=>'required|numeric|min:0.01',
            'skus.*.stock'=>'required|integer|min:0',

        ];
    }


    public function attributes()
    {
        return [
            'title'=>'商品名称',
            'image_id'=>'封面图片id',
            'description'=>'商品描述',
            'on_sale'=>'上架',
            'skus.*.title'=>'SKU 名称',
            'skus.*.description'=>'SKU 描述',
            'skus.*.price'=>'单价',
            'skus.*.stock'=>'库存',
        ];
    }

}
