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

        $xuUrl = UserXuHost::XU_URL . $openid;
        \Log::info('xuUrl = ' . $xuUrl);
        \Log::info('openid  = ' . decrypt($openid));
        header($xuUrl);
    }

    public function xuUserCallBack(Request $request)
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

        $data = json_decode(base64_decode($data));
        if (!isset($data->btk_uid)) {
            return response()->json([
                'code' => 1002,
                'msg' => '参数错误',
           ], 200);
        }

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
