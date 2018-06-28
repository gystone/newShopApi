<?php

namespace App\Http\Controllers\Admin;

use App\Auth\Permission;
use App\Http\Requests\Admin\RoleRequest;
use App\Http\Resources\Admin\RoleCollection;
use App\Models\Admin\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleController extends BaseController
{
    private $role;

    public function __construct(Role $role)
    {
        parent::__construct();
        $this->role = $role;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sort = json_decode(\request()->get('sort'), true);
        $search = json_decode(\request()->get('search'), true);

        $list = $this->role;
        $list = $list->sortAndSearch($list, $sort, $search);

        if (\request()->get('page') == 0) {
            return $this->success(\App\Http\Resources\Admin\Role::collection($list->get()));
        } else {
            return $this->success(new RoleCollection($list->paginate(\request()->get('limit') ?? 10)));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoleRequest $request)
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
        Permission::check('keyboardChart_post');
        return $this->success(new \App\Http\Resources\Admin\Role($role));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(RoleRequest $request, Role $role)
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
