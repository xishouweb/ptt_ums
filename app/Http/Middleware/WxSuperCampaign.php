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

        $wechat = Session::get('super_campaign');

        if (!$wechat) {

            $auth = Factory::officialAccount(config('wechat.official_account.default'));
            $user = $auth->oauth->scopes(['snsapi_userinfo'])
                ->redirect();

            \Log::info('user', [$user]);
//            if ($user['unionid']) {
//                $wechat = WechatUser::createOrUpdate($user, 'unionid', 'yiqi', request()->get('user_id', null));
//                Session::put('wechatUser', $wechat);
//            } else {
//                $openid        = $user['openid'];
//                $wechat_openid = WechatOpenid::where('openid', $openid)->first();
//                if (!$wechat_openid) {
//                    $user = $auth->redirect();
//                    Session::put('wechatUser', $user->all());
//                }
//
//                $wechat = WechatUser::where('unionid', $wechat_openid->unionid)->first();
//                if ($wechat) {
//                    Session::put('wechatUser', $wechat);
//                } else {
//                    $user = $auth->redirect();
//                    Session::put('wechatUser', $user->all());
//                }
//            }
//        }
            return $next($request);
        }
    }
}
