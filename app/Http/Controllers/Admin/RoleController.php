<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\RoleCollection;
use App\Models\Admin\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    private $role;

    public function __construct(Role $role)
    {
        $this->role = $role;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return respond('角色列表', 200, new RoleCollection($this->role->all()));
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

        $attributes = $request->only('name', 'slug');
        $permission_ids = $request->permission_id;

        try {
            $role = $this->role->create($attributes);

            if (isset($permission_ids) && is_array($permission_ids)) {
                $this->insertRolePermission($permission_ids, $role->id);
            }

            DB::commit();
            return respond('添加成功', 200, $role);
        } catch (\Exception $exception) {
            DB::rollBack();
            return respond('添加失败，请稍候重试');
        }
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

        $attributes = $request->only('name', 'slug');
        $permission_ids = $request->permission_id;

        try {
            $role->update($attributes);

            if (isset($permission_ids) && is_array($permission_ids)) {
                DB::table('admin_role_permissions')->where('role_id', $role->id)->delete();
                $this->insertRolePermission($permission_ids, $role->id);
            }

            DB::commit();
            return respond('更新成功', 200, $role);
        } catch (\Exception $exception) {
            DB::rollBack();
            return respond('更新失败，请稍候重试');
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
            DB::table('admin_user_permissions')->where('role_id', $role->id)->delete();
            $role->delete();

            DB::commit();
            return respond('删除成功');
        } catch (\Exception $exception) {
            DB::rollBack();
            return respond('删除失败，请稍候重试');
        }
    }

    private function insertRolePermission(array $permission_ids, int $id) {
        $role_permissions = array();
        foreach ($permission_ids as $k => $v) {
            $role_permissions[$k] = array('permission_id' => $v, 'role_id' => $id);
        }
        DB::table('admin_role_permissions')->insert($role_permissions);
    }
}
