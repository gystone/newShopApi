<?php

namespace App\Http\Middleware;

use App\Models\Admin\AdminUser;
use Closure;

class AdminChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        config(['jwt.user' => 'App\Models\Admin\AdminUser']);
        config(['auth.providers.users.model' => AdminUser::class]);
        return $next($request);
    }
}
