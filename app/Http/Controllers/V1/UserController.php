<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserXuHost;

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

    public function xuUserCallBack()
    {
        $appid = $request->get('appid');
        $timestamp = $request->get('timestamp');
        $data = $request->get('data');
        $sign = $request->get('sign');

        $key = '47886fd0de1asdf135sq22fy56w2kl';
        $secret = '1equEcRkT2hirJhbYByNGCZPRHgFg132rtlb0IZ3vf4=';

          $checkSign = md5($appid . $secret . $timestamp . $data);

        if ($sign != $checkSign) {
           return response()->json([
                'code' => 1003,
                'msg' => 'invalid credentials',
           ], 200);
        }

        $data = json_decode($data);
        $openid = decrypt($data->btk_uid);
        $user = UserXuHost::whereUnionId($openid)->first();

        if (!$user) {
            return response()->json(['code' => 1004, 'msg' => '未找到该用户'], 200);
        }

        $user->xu_host_id = $data->xu_uid;
        $user->xu_nickname = $data->xu_nickname;
        $user->save();

        return response()->json(['code' => 0, 'msg' => 'success'], 200);
    }
}
