<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use Closure;
use Illuminate\Support\Facades\Log;

class WechatConfig
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
        if (empty(config('wechat.official_account.default.app_id')) ||
            empty(config('wechat.official_account.default.secret')) ||
            empty(config('wechat.official_account.default.token')) ||
            empty(config('wechat.official_account.default.aes_key'))) {
            throw new ApiException('微信信息未配置完整', 400);
        }

        return $next($request);
    }
}
