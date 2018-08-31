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
}
