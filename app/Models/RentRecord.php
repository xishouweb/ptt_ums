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

    public static function record($user, $team_id, $token_amount, $token_type, $campaign_id = 1)
    {
        $token = $user->user_token('ptt');

        if (!$token) {
            throw new \Exception('未找到改类型token');
        }


        if ($token_amount > $token->token_amount) {
            throw new \Exception('现有额度不足');
        }

        static::create($user->id, $team_id, $token_amount, $token_type, static::ACTION_JOIN_TEAM, $campaign_id);

        //扣除本身的额度
        static::create($user->id, static::ACTION_SELF_IN . $user->id, -$token_amount, $token_type, static::ACTION_DEDUCTION, $campaign_id);
        //扣除自由额度
        $token->token_amount -= $token_amount;
        //增加锁仓额度
        $token->freeze += $token_amount;

        $token->save();

        ActionHistory::record($user->id,User::ACTION_LOCK_PTT, $team_id, $token_amount,'锁仓PTT', ActionHistory::TYPE_TOKEN);
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

    public function format()
    {

        if (substr($this->team_id, 0, 8) == self::ACTION_SELF_IN) {
            $user_id = intval(substr($this->team_id, 8));

            $user = User::where("id", $user_id)->first();

            $team['team_id'] = $this->team_id;
            $team['team_name'] = $user->nickname;
            $team['logo'] = $user->avatar;
            $team['info'] = null;
            $team['type'] = 'personal';
            $team['count'] = 1;

        } else {

            $team = Team::find($this->team_id)->format();
        }

        $team['credit'] = $this->getTeamCredit();
        $team['ranking_id'] = DataCache::getZrank($this->team_id);
        $team['token_amount'] = $this->total;

        return $team;
    }


    public function getTeamCredit()
    {
        return $this->total * User::CREDIT_TOKEN_RATIO + TokenVote::totalVoteOf($this->team_id) * User::CREDIT_VOTE_RATIO;
    }
}
