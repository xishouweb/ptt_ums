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
        dd($wechatInfo);
        $wechatUser = $wechatInfo['original'];



    }

    public function rank()
    {

    }
}