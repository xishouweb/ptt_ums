<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentRecord extends Model
{

    const ACTION_JOIN_TEAM = 'join_team';
    const ACTION_JOIN_CAMPAIGN = 'join_campaign';
    const ACTION_DEDUCTION = 'deduction';

    const ACTION_SELF_IN = 'self_in_';

    public static function record($user, $team_id, $token_amount, $token_type, $campaign_id)
    {
        $token = $user->token($token_type);

        if (!$token) {
            throw new \Exception('未找到改类型token');
        }


        if ($token_amount > $token->amount){
            throw new \Exception('token额度不足');
        }

        static::create($user->id, $team_id, $token_amount, $token_type, static::ACTION_JOIN_TEAM, $campaign_id);

        //扣除本身的额度
        static::create($user->id, $team_id, -$token_amount, $token_type, static::ACTION_DEDUCTION, $campaign_id);
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

    public static function ranking($campaign_id, $token_type, array $indexs)
    {
        $ranks = RentRecord::where('campaign_id', $campaign_id)
            ->where('token_type', $token_type)
            ->whereIn('action', [RentRecord::ACTION_JOIN_CAMPAIGN, RentRecord::ACTION_JOIN_TEAM])
            ->groupBy('team_id')
            ->select('team_id', \DB::raw("SUM(token_amount) as total"))
            ->orderBy('total', 'desc')
            ->get();

        foreach ($ranks as $key => $rank) {
            foreach ($indexs as  $index) {
                if ($rank->team_id == $index) {
                    $data[$index] = $key +1;
                }
            }
        }

        return $data;
    }
}
