<?php

namespace App\Http\Middleware;

use Closure;
use EasyWeChat\Factory;
use Illuminate\Support\Facades\Session;

class WxSuperCampaign
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $session = Session::get('wechat.oauth_user.default');

        if ($session) {
            \Log::info('session = ', [$session]);
        }


        return $next($request);
    }
}
