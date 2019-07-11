<?php
/**
 * Created by sublime.
 * User: erdangjia
 * Date: 2019/7/12
 * Time: 10:41
 */

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Models\UserCampaign;

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

        $userRank = null;
        $record = UserCampaign::whereUserId($user->id)->whereCampaignId()->first();
        if ($record) {
            $userRank = $this->rank(1, $user->id);
        }

        $rankList = $this->rank();

        return view('campaign.price_query_rank')->compact(['userRank' , 'rankList']);
    }

    public function rank($page = 1, $user_id = null)
    {
        $sql = "select c.*, users.nickname, users.avatar from (select a.*, (@rowNum:=@rowNum+1) AS rank from
(select user_id,count(1) gt, sum(query_count) total from price_query_statistics  GROUP BY user_id ORDER BY total DESC, gt desc) as a,
(SELECT (@rowNum :=0) ) b) as c left join users on c.user_id = users.id ";

        if ($user_id) {
            $userSql = $sql . " where c.user_id = " . $user_id;
            $data = \DB::select($userSql);

            return $data ? $data[0] : null;
        }

        $sql .= "limit " . ($page - 1) * 10 . " , 10";
        $data = \DB::select($sql);

        $countQuery = "select count(1) as total_size from (select a.*, (@rowNum:=@rowNum+1) AS rank from
(select user_id,count(1) gt, sum(query_count) from price_query_statistics  GROUP BY user_id ) as a,
(SELECT (@rowNum :=0) ) b) as c";
        $count = \DB::select($countQuery);

        return [
            'data' => $data,
            'page' => $page,
            'page_size' => 10,
            'total_size' => $count[0]->total_size,
        ];
    }
}