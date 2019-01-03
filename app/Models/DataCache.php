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

    public static function getSymbolPrice($symbol)
    {
        $key = 'coinmarketcap_price_of_' . $symbol;

        return json_decode(Redis::get($key), true);
    }

    public static function setSymbolPrice($symbol, $data)
    {
        $key = 'coinmarketcap_price_of_' . $symbol;

        Redis::set($key, json_encode($data), 'EX',1800);
    }
}
