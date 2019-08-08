<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function redirectXu()
    {
        $wechatInfo = session('wechat.oauth_user.user_base'); // 拿到授权用户资料
        $wechatUser = $wechatInfo['original'];
        $openid =  encrypt($wechatUser['openid']);
        $xuUrl = 'Location: http://xzs.jinqunla.com/step2Preview?vcSerialNo=0023a1e3447fdb31836536cc903f1310.1';
        $xuUrl = $xuUrl . '&btk_uid=' . $openid;
        \Log::info('xuUrl = ' . $xuUrl);
        \Log::info('openid  = ' . decrypt($openid));
        header($xuUrl);
    }
}
