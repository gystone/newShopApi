<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UserAddressRequest;
use App\Models\UserAddress;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserAddressesController extends Controller
{
    use ApiResponse;
    protected $user;

    public function __construct()
    {
        auth()->shouldUse('api');
        $this->user = auth()->user();
    }

    public function index()
    {
        $addresses = $this->user->addresses()->orderBy('last_used_at','desc')->orderBy('id','desc')->get();
        return $this->success($addresses, '地址列表');
    }

    public function store(UserAddressRequest $request)
    {
        $re = $this->user->addresses()->create([
            'province' => $request->province,
            'city' => $request->city,
            'district' => $request->district,
            'address' => $request->address,
            'zip' => $request->zip ?? 0,
            'contact_name' => $request->contact_name,
            'contact_phone' => $request->contact_phone,
        ]);

        if ($re) {
            return $this->success([], '添加成功');
        } else {
            return $this->failed('添加失败');
        }

    }

    public function show(UserAddress $userAddress)
    {

        $this->authorize('own', $userAddress);

        return $this->success($userAddress, '地址详情');
    }

    public function update(UserAddress $userAddress, UserAddressRequest $request)
    {
        $this->authorize('own', $userAddress);

        $re = $userAddress->update([
            'province' => $request->province,
            'city' => $request->city,
            'district' => $request->district,
            'address' => $request->address,
            'zip' => $request->zip ?? 0,
            'contact_name' => $request->contact_name,
            'contact_phone' => $request->contact_phone,
        ]);

        if ($re) {
            return $this->success([], '修改成功');
        } else {
            return $this->failed('修改失败');
        }
    }

    public function destroy(UserAddress $userAddress)
    {
        $this->authorize('own', $userAddress);
        $re = $userAddress->delete();

        if ($re) {
            return $this->success([], '删除成功');
        } else {
            return $this->failed('删除失败');
        }
    }


}
