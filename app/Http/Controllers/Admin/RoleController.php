<?php

namespace App\Http\Controllers\Admin;

use App\Auth\Permission;
use App\Http\Controllers\ApiController;
use App\Http\Resources\Admin\RoleCollection;
use App\Models\Admin\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleController extends ApiController
{
    private $role;

    public function __construct(Role $role)
    {
        auth()->shouldUse('api_admin');
        $this->role = $role;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->success(new RoleCollection($this->role->all()));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        $attributes = $request->only('name', 'permissions');

        try {
            $role = $this->role->create($attributes);

            DB::commit();
            return $this->success($role);
        } catch (\Exception $exception) {Log::info($exception->getMessage());
            DB::rollBack();
            return $this->failed('添加失败，请稍候重试');
        }
    }

    public function show(Role $role)
    {
        return new \App\Http\Resources\Admin\Role($role);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        DB::beginTransaction();

        $attributes = $request->only('name', 'permissions');

        try {
            $role->update($attributes);

            DB::commit();
            return $this->success($role);
        } catch (\Exception $exception) {Log::info($exception->getMessage());
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
    public function destroy(Role $role)
    {
        DB::beginTransaction();

        try {
            $role->delete();

            DB::commit();
            return $this->message('删除成功');
        } catch (\Exception $exception) {Log::info($exception->getMessage());
            DB::rollBack();
            return $this->failed('删除失败，请稍候重试');
        }
    }
}
