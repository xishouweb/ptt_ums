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
use App\Models\Campaign;

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
        $record = UserCampaign::whereUserId($user->id)->whereCampaignId(2)->first();
        if ($record) {
            $userRank = $this->rank(1, $user->id);
        }
        $userJoin = $record ? 1 : 0;

        $rankList = $this->rank();

        return view('campaign.price_query_rank')->with(compact('userRank' , 'rankList', 'userJoin', 'user'));
    }

    public function rank($page = 1, $user_id = null)
    {
//         $sql = "select c.*, users.nickname, users.avatar from (select a.*, (@rowNum:=@rowNum+1) AS rank from
// (select user_id,count(1) as group_count, sum(query_count) total from price_query_statistics  GROUP BY user_id ORDER BY total DESC, group_count desc) as a,
// (SELECT (@rowNum :=0) ) b) as c left join users on c.user_id = users.id ";
        $sql = "select c.*, user_xu_hosts.xu_nickname from (select a.*, (@rowNum:=@rowNum+1) AS rank from
(select user_id,count(1) as group_count, sum(query_count) total from price_query_statistics  GROUP BY xu_host_id ORDER BY total DESC, group_count desc) as a,
(SELECT (@rowNum :=0) ) b) as c left join user_xu_hosts on c.xu_host_id = user_xu_hosts.xu_host_id ";

        if ($user_id) {
            $userSql = $sql . " where c.user_id = " . $user_id;
            $data = \DB::select($userSql);

            return $data ? $data[0] : null;
        }

        $sql .= "limit " . ($page - 1) * 10 . " , 10";
        $data = \DB::select($sql);

//         $countQuery = "select count(1) as total_size from (select a.*, (@rowNum:=@rowNum+1) AS rank from
// (select user_id,count(1) gt, sum(query_count) from price_query_statistics  GROUP BY user_id ) as a,
// (SELECT (@rowNum :=0) ) b) as c";
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

    public function join($user_id, $campaign_id)
    {
        $campaign = Campaign::find($campaign_id);
        if (!$campaign){
            return $this->apiResponse([], '未找到该活动!', 1);
        }
        $now = date('Y-m-d H:i:s');
        if ($now > $campaign->end_date) {
            return $this->apiResponse([], '活动已结束!', 1);
        }

        if ($now < $campaign->start_date) {
            return $this->apiResponse([], '活动未开始!', 1);
        }

        $record = UserCampaign::whereUserId($user_id)->whereCampaignId($campaign_id)->first();

        if ($record) {
            return $this->apiResponse([], '已经加入该活动', 1);
        }

        $uc = UserCampaign::create([
            'user_id' => $user_id,
            'campaign_id' => $campaign_id
        ]);

        if ($uc) {
            return $this->apiResponse([], '加入成功', 0);
        }

        return $this->apiResponse([], '加入失败', 1);
    }
}