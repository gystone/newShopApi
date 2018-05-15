<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Admin\PermissionRequest;
use App\Http\Resources\Admin\PermissionCollection;
use App\Models\Admin\Permission;
use Illuminate\Support\Facades\DB;

class PermissionController extends ApiController
{
    private $permission;

    public function __construct(Permission $permission)
    {
        auth()->shouldUse('api_admin');
        $this->permission = $permission;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->success(new PermissionCollection($this->permission->all()));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PermissionRequest $request)
    {
        DB::beginTransaction();

        $attributes = $request->only(['name', 'slug', 'http_method', 'http_path']);

        try {
            $permission = $this->permission->create($attributes);

            DB::commit();
            return $this->success($permission);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->failed('添加失败，请稍候重试');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PermissionRequest $request, Permission $permission)
    {
        DB::beginTransaction();

        $attributes = $request->only('name', 'slug', 'http_method', 'http_path');

        try {
            $permission->update($attributes);

            DB::commit();
            return $this->success($permission);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->failed('更新失败，请稍候重试');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        DB::beginTransaction();

        try {
            $permission->delete();

            DB::commit();
            return $this->message('删除成功');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->failed('删除失败，请稍候重试');
        }
    }
}
