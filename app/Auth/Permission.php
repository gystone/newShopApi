<?php

namespace App\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class Permission
{
    public static function check($permission)
    {
        if (auth('api_admin')->user()->isAdministrator()) {
            return true;
        }

        if (is_array($permission)) {
            collect($permission)->each(function ($permission) {
                call_user_func([Permission::class, 'check'], $permission);
            });

            return;
        }

        if (auth('api_admin')->user()->isCannot($permission)) {
            static::error();
        }

        return true;
    }

    public static function error()
    {
        $response = response([
            'status' => 'error',
            'code' => 400,
            'message' => '拒绝访问，权限不足'
        ], 400);
        return static::respond($response);
    }

    public static function respond(Response $response)
    {
        $next = function () use ($response) {
            return $response;
        };

        (new static())->handle(Request::capture(), $next)->send();

        exit;
    }

    public function handle($request, \Closure $next)
    {
        $response = $next($request);

        if (auth('api_admin')->guest()) {
            return $response;
        }

        return $response;
    }
}