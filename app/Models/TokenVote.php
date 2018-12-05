<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TokenVote extends Model
{
    protected $guarded = ['id'];

    public static function record($team_id, $user_id, $amount)
    {
        return static::create([
            'team_id' => $team_id,
            'user_id' => $user_id,
            'amount' => $amount,
            'consecutive_days' => self::check_consecutive($user_id)
        ]);
    }

    public static function check_consecutive($user_id)
    {
        $model = static::where('user_id', $user_id)->orderBy('id', 'desc')->first();

        if ($model && $model->created_at > date('Y-m-d 00:00:00', strtotime('-1 day'))) {
            return $model->consecutive_days + 1;
        }

        return 1;
    }

    public static function totalVoteOf($team_id, $user_id = null)
    {
        $vote = self::where('team_id', $team_id);

        if ($user_id) {
            $vote->whereUserId($user_id);
        }
        return $vote->sum('amount') ?? 0;
    }

    public function format()
    {
        $data['votes'] = $this->total;
        $data['vote_ranking_id'] = self::ranking($this->team_id)['ranking_id'];

        if (substr($this->team_id, 0, 8) == RentRecord::ACTION_SELF_IN) {
            $user_id = intval(substr($this->team_id, 8));

            if ($user = User::where("id", $user_id)->first()) {

                $data['team_id'] = $this->team_id;
                $data['team_name'] = $user->nickname;
                $data['logo'] = $user->avatar;
                $data['info'] = null;
                $data['type'] = 'personal';
                $data['count'] = 1;
            } else {
                throw new \Exception('未找到该用户');
            }

        } else {
            $team = Team::find($this->team_id)->format();
            $data = array_merge($data, $team);
        }

        $data['credit'] = $this->getTeamCredit();
        $data['ranking_id'] = DataCache::getZrank($this->team_id);
        $data['token_amount'] = $this->getTokenAmount($this->team_id);

        return $data;
    }

    public function getTeamCredit()
    {
        return $this->getTokenAmount($this->team_id) * User::CREDIT_TOKEN_RATIO + $this->total  * User::CREDIT_VOTE_RATIO;
    }

    public function getTokenAmount($team_id, $token_type = 'ptt', $campaign_id = 1)
    {
        return RentRecord::where('team_id', $team_id)
            ->where('token_type', $token_type)
            ->whereIn('action', [RentRecord::ACTION_JOIN_CAMPAIGN, RentRecord::ACTION_JOIN_TEAM, RentRecord::ACTION_DEDUCTION])
            ->where('campaign_id', $campaign_id)
            ->sum('token_amount') ?? 0;
    }
    public static function ranking($team_id)
    {
        $ranks = TokenVote::groupBy('team_id')
            ->select('team_id', DB::raw("SUM(amount) as total"))
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
}
