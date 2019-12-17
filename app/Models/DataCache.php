<?php

namespace App\Models;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

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

    public static function getPriceOf($symbol)
    {
        $key = 'coinmarketcap_price_of_' . $symbol;

        return json_decode(Redis::get($key), true);
    }

    public static function setPriceOf($symbol, $data)
    {
        $key = 'coinmarketcap_price_of_' . $symbol;

        Redis::set($key, json_encode($data), 'EX',900);
    }

    public static function getSymbolsPrice()
    {
        $key = 'coinmarketcap_price_of_symbols';

        return json_decode(Redis::get($key), true);
    }

    public static function setSymbolsPrice($data)
    {
        $key = 'coinmarketcap_price_of_symbols';

        Redis::set($key, json_encode($data), 'EX', 900);
    }

    public static function getCurrency($currency)
    {
        $currency = strtoupper($currency);

        if ($currency === 'USD') return 1;

        $key = 'currency_cache_for_' . $currency;
        $value = Redis::get($key);

        if (!$value) {
            $url = "http://op.juhe.cn/onebox/exchange/currency?from=USD&to=$currency&key=4cdacbeb5039b14c171171f7a3d0e4b1";
            $client = new Client();

            $res = $client->request('GET', $url);
            $result  = json_decode((string) $res->getBody());

            if ($result->error_code != 0 ) {
                throw new \Exception('The currency exchange rate was not found');
            }

            $data= $result->result;

            $value = $data[0]->exchange;

            Redis::set($key, $value, 'EX', 60 * 30);
        }

        return $value;
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
        return Redis::zrevrank('credit_rank', $key) + 1;
    }

    public static function getSymbols($key)
    {
        return json_decode(Redis::get($key), true);
    }

    public static function setSymbolsFor($key, $data)
    {
        Redis::set($key, json_encode($data));
    }

    public static function getBaseSymbolPrice($symbol)
    {
        $key = 'base_price_' . $symbol;
        return json_decode(Redis::get($key), true);
    }

    public static function setBaseSymbolsPrice($symbol, $data)
    {
        $key = 'base_price_' . $symbol;
        Redis::set($key, json_encode($data), 'EX', 15);
    }

    public static function getSymbolInfo($key)
    {
        return json_decode(Redis::get($key), true);
    }

    public static function setSymbolInfo($key, $data)
    {
        Redis::set($key, json_encode($data), 'EX', 10);
    }

    public static function lock($key, $time)
    {
        return Redis::set($key, 1, 'EX' , $time, 'NX');
    }

    public static function callTotal()
    {
        $key = 'wechat_robot_callback_count';
        Redis::incr($key);
    }

    public static function zincrOfScoreFor($symbol, $score)
    {
        Redis::zIncrBy('wechat_robot_callback_detail', $score, $symbol);
    }

    public static function getAllSymbolCount()
    {
        $key = 'wechat_robot_callback_count';
        return Redis::get($key);
    }

    public static function getSymbolCountDetail()
    {
        return Redis::zrevrange('wechat_robot_callback_detail', 0, -1, 'WITHSCORES');
    }

    public static function getSymbolYesterdayLastPrice($key)
    {
        $key = 'symbol-yesterday-last-price-' . $key;
        return Redis::get($key);
    }

    public static function setSymbolYesterdayLastPrice($key, $data)
    {
        $key = 'symbol-yesterday-last-price-' . $key;

        $flagTime = strtotime(date('Y-m-d 08:00:00'));
        $time = time();
        if ($time >= $flagTime) {
            $expire = 86400 - ($time - $flagTime);
        } else {
            $expire = $flagTime - $time - 1;
        }

        Redis::set($key, $data, 'EX', $expire);
    }
}
