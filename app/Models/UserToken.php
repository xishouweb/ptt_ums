<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    protected $guarded = ['id'];

    public static function record($user_id, $token_amount, $token_type, $freeze = 0, $votes = 0, $temp_votes = 0)
    {
        return static::create([
            'user_id' => $user_id,
            'token_amount' => $token_amount,
            'token_type' => $token_type,
            'freeze' => $freeze,
            'votes' => $votes,
            'temp_votes' => $temp_votes,
        ]);
    }
}
