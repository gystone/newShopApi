<?php

namespace App\Auth;

use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;

class Permission
{
    public static function check($permission)
    {
        if (is_array($permission)) {
            collect($permission)->each(function ($permission) {
                call_user_func([Permission::class, 'check'], $permission);
            });

            return;
        }

        if (auth('api_admin')->user()->isCannot($permission)) {Log::info(1231);
            static::error();
        }
    }

    public static function error()
    {Log::info('kaka');dd('拒绝访问，权限不足');
        return response()->json('拒绝访问，权限不足', 400);
    }
}