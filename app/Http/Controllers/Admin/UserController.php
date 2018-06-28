<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UpdateInfoRequest;
use App\Http\Requests\Admin\UserRequest;
use App\Http\Resources\Admin\User;
use App\Http\Resources\Admin\UserCollection;
use App\Models\Admin\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{
    private $user;

    public function __construct(AdminUser $user)
    {
        parent::__construct();
        $this->user = $user;
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

        $list = $this->user;
        $list = $list->sortAndSearch($list, $sort, $search);

        if (\request()->get('page') == 0) {
            return $this->success(User::collection($list->get()));
        } else {
            return $this->success(new UserCollection($list->paginate(\request()->get('limit') ?? 10)));
        }
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

        try {
            $user = $this->user->create($attributes);

            if (isset($role_ids)) {
                $this->insertRoleUser($role_ids, $user->id);
            }

            DB::commit();

            return $this->success(new User($user));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->failed('添加失败，请稍候重试');
        }
    }

    public function show(AdminUser $user)
    {
        return $this->success(new User($user));
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
 
        DB::beginTransaction();

        try {
            $user->update($attributes);

            if (isset($role_ids)) {
                DB::table('admin_role_users')->where('user_id', $user->id)->delete();
                $this->insertRoleUser($role_ids, $user->id);
            }

            DB::commit();

            return $this->success(new User($user));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->failed('更新失败');
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
            $user->delete();
            DB::commit();
            return $this->message('删除成功');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->failed('删除失败，请稍候重试');
        }
    }

    private function insertRoleUser($role_ids, int $id) {
        $role_users = array();
        if (is_array($role_ids)) {
            foreach ($role_ids as $k => $v) {
                $role_users[$k] = array('role_id' => $v, 'user_id' => $id);
            }
        } else {
            $role_users = array('role_id' => $role_ids, 'user_id' => $id);
        }

        DB::table('admin_role_users')->insert($role_users);
    }

    public function uploadAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|mimes:jpeg,bmp,png,gif',
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors()->first());
        }
        $path = $request->file('avatar')->store('images/avatars','admin');

        return $this->success(['path' => $path, 'url' => Storage::disk('admin')->url($path)]);
    }

    public function updateInfo(UpdateInfoRequest $request)
    {
        $attributes = $request->only('name', 'avatar', 'password');

        $user = AdminUser::find(auth('api_admin')->user()->id);
        $res = $user->update($attributes);

        if ($res) {
            return $this->success($user);
        } else {
            return $this->failed('更新失败，请稍候重试');
        }
    }
}
