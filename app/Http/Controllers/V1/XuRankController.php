<?php
/**
 * Created by sublime.
 * User: erdangjia
 * Date: 2019/7/12
 * Time: 10:41
 */

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;

class XuRankController extends Controller
{
    public function index()
    {
        $wechatInfo = session('wechat.oauth_user.default'); // 拿到授权用户资料
        $wechatUser = $wechatInfo['original'];

        if ($wechatInfo['email']) {
            $user = User::whereEmail($wechatInfo['email'])->orWhereUnionid($wechatUser['unionid'])->first();
            if (!$user) {
                $user = User::create([
                    'email' => $wechatInfo['email'],
                    'unionid' => $wechatUser['unionid'],
                    'nickname' => $wechatUser['nickname'],
                    'avatar' => $wechatUser['headimgurl'],
                    'country' => $wechatUser['country'],
                    'type' => User::TYPE_CAMPAIGN,
                    'password' => Hash::make($wechatInfo['email']),
                    'channel' => 'price_query_xu',
                ]);
            } elseif (!$user->unionid) {
                $user->unionid = $wechatUser['unionid'];
                $user->save();
            }
        } else {
            $user = User::whereUnionid($wechatUser['unionid'])->first();

            if (!$user) {
                $user = User::create([
                    'unionid' => $wechatUser['unionid'],
                    'nickname' => $wechatUser['nickname'],
                    'avatar' => $wechatUser['headimgurl'],
                    'country' => $wechatUser['country'],
                    'type' => User::TYPE_CAMPAIGN,
                    'password' => Hash::make(substr($wechatUser['unionid'], 18, 8)),
                    'channel' => 'price_query_xu',
                ]);
            }
        }
        dd($user);
        return view('campaign.price_query_rank')->with(['user' => $user]);
    }

    public function rank()
    {

    }
}