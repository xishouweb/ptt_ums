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

        $user = Session::get('wechat.oauth_user.default');

        if ($user) {
            \Log::info('session = ', [$user]);
        }

        dd();


        return $next($request);
    }
}
