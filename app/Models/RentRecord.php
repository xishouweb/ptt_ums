<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class RentRecord extends Model
{

    const ACTION_JOIN_TEAM = 'join_team';
    const ACTION_JOIN_CAMPAIGN = 'join_campaign';
    const ACTION_DEDUCTION = 'deduction';

    const ACTION_SELF_IN = 'self_in_';

    public static function record($user, $team_id, $token_amount, $token_type, $campaign_id)
    {
        $token = $user->user_token('ptt');

        if (!$token) {
            throw new \Exception('未找到改类型token');
        }


        if ($token_amount > $token->freeze) {
            throw new \Exception('token额度不足');
        }

        static::create($user->id, $team_id, $token_amount, $token_type, static::ACTION_JOIN_TEAM, $campaign_id);

        //扣除本身的额度
        static::create($user->id, $team_id, -$token_amount, $token_type, static::ACTION_DEDUCTION, $campaign_id);
        //扣除自由额度
        $token->token_amount -= $token_amount;
        //增加锁仓额度
        $token->freeze += $token_amount;

        $token->save();
    }


    public static function create($user_id, $team_id, $token_amount, $token_type, $action, $campaign_id)
    {
        $rentRcord = new self();
        $rentRcord->user_id = $user_id;
        $rentRcord->team_id = $team_id;
        $rentRcord->token_amount = $token_amount;
        $rentRcord->token_type = $token_type;
        $rentRcord->action = $action;
        $rentRcord->campaign_id = $campaign_id;
        $rentRcord->save();

        return $rentRcord;
    }

    public static function ranking($campaign_id, $token_type, $team_id)
    {
        $ranks = RentRecord::where('campaign_id', $campaign_id)
            ->where('token_type', $token_type)
            ->whereIn('action', [RentRecord::ACTION_JOIN_CAMPAIGN, RentRecord::ACTION_JOIN_TEAM])
            ->groupBy('team_id')
            ->select('team_id', \DB::raw("SUM(token_amount) as total"))
            ->orderBy('total', 'desc')
            ->get();

        foreach ($ranks as $key => $rank) {

            if ($rank->team_id == $team_id) {
                $rank['ranking_id'] = $key + 1;
                return $rank;
            }
        }

        return [];
    }

    public function format($source = [])
    {

        if (substr($this->team_id, 0, 8) == self::ACTION_SELF_IN) {
            $user_id = intval(substr($this->team_id, 8));

            if ($user = User::where("id", $user_id)->first()) {
                $rank = self::ranking($source['campaign_id'], $source['token_type'], $this->team_id);
                $old_model = DataCache::getRanking($this->team_id);
                $status = $rank['ranking_id'] >= $old_model['ranking_id'] ? 'up' : 'down';

                return [
                    'team_name' => $user->nickname,
                    'logo' => $user->avatar,
                    'info' => null,
                    'type' => 'personal',
                    'credit' => $rank['total'] * 1,
                    'ranking_id' => $rank['ranking_id'],
                    'status' => $status,
                ];
            } else {
                throw new \Exception('未找到该用户');
            }

        }
        return Team::find($this->team_id)->format($source);
    }
}
