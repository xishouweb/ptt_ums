<?php
/**
 * Created by PhpStorm.
 * User: Kana
 * Date: 2018/9/13
 * Time: 16:41
 */

namespace App\Http\Controllers\App;


use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;

class ToolController extends Controller
{
    const GET_PRICE_URL = 'https://api.coinmarketcap.com/v2/ticker/';

    public function getPrice()
    {
        if (Redis::exists('wallet:price:list')) {
            $data = Redis::get('wallet:price:list');
        } else {
            $http = new Client(['verify' => false]);
            $response = $http->get(self::GET_PRICE_URL);
            $data = (string)$response->getBody();
            Redis::setex('wallet:price:list', 300, $data);
        }
        $data = json_decode($data, true);
        return response()->json($data);
    }
}