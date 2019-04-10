<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\AddCouponRequest;
use App\Models\CouponCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CouponCodeController extends BaseController
{
    public function index()
    {
        $list = CouponCode::orderBy('created_at', 'desc')->get();

        return $this->success($list, '优惠券列表');
    }

    public function store(AddCouponRequest $request)
    {

        //todo 简写后续待修改
        $re = CouponCode::create($request->all);

        if($re){
            return $this->success([], '添加成功');

        }else{
            return $this->failed('添加失败');
        }

    }

    public function update()
    {
        //todo 后续
    }

    public function destroy()
    {
        //todo 后续
    }

}
