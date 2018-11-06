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

    public static function zAddIntoCreditRank($key, $score)
    {
       return Redis::zadd('credit_rank', $score, $key);
    }

    public static function getRangOfCreditRank($start, $end)
    {
        return Redis::zrevrange('credit_rank', $start, $end);
    }

    public static function zincrOfCreditRankFor($key, $score)
    {
        return Redis::zIncrBy('credit_rank', $score, $key);
    }

    public static function getCountOfCreditRank()
    {
        return Redis::zcard('credit_rank');
    }

    public static function getZscoreOfCreditRank($key)
    {
        return Redis::zscore('credit_rank', $key);
    }

    public static function getZrank($key)
    {
        return Redis::zrevrank('credit_rank', $key);
    }

}
