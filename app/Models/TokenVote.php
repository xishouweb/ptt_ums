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

    public static function totalVoteOf($team_id)
    {
        return self::where('team_id', $team_id)->sum('amount') ?? 0;
    }

    public function format($source)
    {
        $data['team_id'] = $this->team_id;
        $data['total'] = $this->total;
        $data['ranking_id'] = self::ranking($this->team_id);

        if (substr($this->team_id, 0, 8) == RentRecord::ACTION_SELF_IN) {
            $user_id = intval(substr($this->team_id, 8));

            if ($user = User::where("id", $user_id)->first()) {
                $data['team_name'] = $user->nick_name;
                $data['logo'] = $user->avatar;
                $data['type'] = 'personal';
            } else {
                throw new \Exception('未找到该用户');
            }

        } else {
            $team = Team::find($this->team_id);
            $data['team_name'] =  $team->team_name;
            $data['logo'] = $team->logo;
            $data['type'] = 'team';
        }

        return $data;
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
