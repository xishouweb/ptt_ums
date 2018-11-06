<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Redis;

class DataCache extends Model
{
    public static function putRanking($rank)
    {
        $key = 'super_agency_ranking_' . $rank['team_id'];

        Redis::set($key, json_encode($rank));
    }

    public static function getRanking($team_id)
    {
        $key = 'super_agency_ranking_' . $team_id;

        return json_decode(Redis::get($key), true);
    }
}
