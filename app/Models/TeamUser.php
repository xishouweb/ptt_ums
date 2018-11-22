<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamUser extends Model
{
    protected $guarded = ['id'];
    public static function record($team_id, $user_id, $campaign_id = 1)
    {
        return static::create([
            'team_id' => $team_id,
            'user_id' => $user_id,
            'campaign_id' => $campaign_id,
        ]);
    }
}
