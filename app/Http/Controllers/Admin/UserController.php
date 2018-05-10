<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UserRequest;
use App\Http\Resources\Admin\User;
use App\Http\Resources\Admin\UserCollection;
use App\Models\Admin\AdminUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private $user;

    public function __construct(AdminUser $user)
    {
        auth()->shouldUse('api_admin');
        $this->user = $user;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return respond('管理员列表', 200, new UserCollection($this->user->all()));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        DB::beginTransaction();

        $attributes = $request->only(['username', 'name', 'avatar', 'password']);
        $role_ids = $request->role_id;
        $permission_ids = $request->permission_id;

        try {
            $user = $this->user->create($attributes);

            if (isset($role_ids) && is_array($role_ids)) {
                $this->insertRoleUser($role_ids, $user->id);
            }

            if (isset($permission_ids) && is_array($permission_ids)) {
                $this->insertUserPermission($permission_ids, $user->id);
            }

            DB::commit();

            return respond('添加成功', 200, $user);
        } catch (\Exception $exception) {
            DB::rollBack();
            return respond('添加失败，请稍候重试', 200);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, AdminUser $user)
    {
        $attributes = $request->only(['username', 'name', 'avatar', 'password']);
        $role_ids = $request->role_id;
        $permission_ids = $request->permission_id;

        DB::beginTransaction();

        try {
            $user->update($attributes);

            if (isset($role_ids) && is_array($role_ids)) {
                DB::table('admin_role_users')->where('user_id', $user->id)->delete();
                $this->insertRoleUser($role_ids, $user->id);
            }

            if (isset($permission_ids) && is_array($permission_ids)) {
                DB::table('admin_user_permissions')->where('user_id', $user->id)->delete();
                $this->insertUserPermission($permission_ids, $user->id);
            }


            DB::commit();

            return respond('更新成功', 200, $user);
        } catch (\Exception $exception) {
            DB::rollBack();
            return respond('更新失败', 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(AdminUser $user)
    {
        DB::beginTransaction();

        try {
            DB::table('admin_role_users')->where('user_id', $user->id)->delete();
            DB::table('admin_user_permissions')->where('user_id', $user->id)->delete();
            $user->delete();
            DB::commit();
            return respond('删除成功');
        } catch (\Exception $exception) {
            DB::rollBack();
            return respond('删除失败，请稍候重试');
        }
    }

    private function insertRoleUser(array $role_ids, int $id) {
        $role_users = array();
        foreach ($role_ids as $k => $v) {
            $role_users[$k] = array('role_id' => $v, 'user_id' => $id);
        }
        DB::table('admin_role_users')->insert($role_users);
    }

    private function insertUserPermission(array $permission_ids, int $id) {
        $user_permissions = array();
        foreach ($permission_ids as $k => $v) {
            $user_permissions[$k] = array('permission_id' => $v, 'user_id' => $id);
        }
        DB::table('admin_user_permissions')->insert($user_permissions);
    }

    public function uploadAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|mimes:jpeg,bmp,png,gif',
        ]);

        if ($validator->fails()) {
            return respond($validator->errors()->first(), 400);
        }
        $path = $request->file('avatar')->store('images/avatars','admin');

        return respond('上传成功', 200, ['path' => $path]);
    }
}
