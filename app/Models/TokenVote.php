<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function format($source)
    {
        $data['team_id'] = $this->team_id;
        $data['total'] = $this->total;

        if (substr($this->team_id, 0, 8) == RentRecord::ACTION_SELF_IN) {
            $user_id = intval(substr($this->team_id, 8));

            if ($user = User::where("id", $user_id)->first()) {
                $data['team_name'] = $user->nick_name;
            } else {
                throw new \Exception('未找到该用户');
            }

        } else {
            $data['team_name'] = Team::find($this->team_id)->team_name;
        }

        return $data;
    }
}
