<?php
/**
 * Created by PhpStorm.
 * User: Kana
 * Date: 2018/9/13
 * Time: 16:41
 */

namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use App\Models\Captcha;
use App\Models\Contract;
use App\Models\DataCache;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use QL\QueryList;

class ToolController extends Controller
{
    public function getPrice($symbol)
    {

        $count = 0;
        if ($lbankPrice = $this->__getPriceFromLbank($symbol)) {
            $count ++;
        }

        $symbol = str_replace('_', '', $symbol);

        if ($binancePrice = $this->__getPriceFromBinance($symbol)) {
            $count ++;
        }

        if ($huoBiPrice = $this->__getPriceFromHuoBi($symbol)) {
            $count ++;
        }

        if ($cointigerPrice = $this->__getPriceFromCointiger($symbol)) {
            $count ++;
        }

        return ($binancePrice + $huoBiPrice + $cointigerPrice + $lbankPrice) / $count;
    }

    private function __getPriceFromBinance($symbol)
    {
        $symbol = strtoupper($symbol);
        $url = 'https://api.binance.com/api/v3/ticker/price?symbol=';
        $client = new Client();
        $res = $client->request('GET', $url . $symbol);
        $resData  = json_decode((string) $res->getBody());

        return isset($resData->price) ? $resData->price : 0;
    }

    private function __getPriceFromHuoBi($symbol)
    {
        $url='https://api.huobi.pro/market/trade?symbol=';
        $client = new Client();
        $res = $client->request('GET', $url . $symbol);
        $resData  = json_decode((string) $res->getBody());

        if ($resData->status == 'ok') {
            return isset($resData->tick) && isset($resData->tick->data) ? $resData->tick->data[0]->price : 0;
        } else {
            return 0;
        }
        return isset($resData->price) ? $resData->price : 0;
    }

    private function __getPriceFromCointiger($symbol)
    {
        $url = 'https://api.cointiger.com/exchange/trading/api/market/history/trade?symbol=';
        $client = new Client();
        $res = $client->request('GET', $url . $symbol);
        $resData  = json_decode((string) $res->getBody());

        if ($resData->code == '0') {
            return isset($resData->data) && isset($resData->data->trade_data) ? $resData->data->trade_data[0]->price : 0;
        } else {
            return 0;
        }
    }

    private function __getPriceFromLbank($symbol)
    {
        $url = 'https://www.lbkex.net/v1/trades.do?size=1&symbol=';
        $client = new Client();
        $res = $client->request('GET', $url . $symbol);
        $resData  = json_decode((string) $res->getBody());

        if (isset($resData[0])) {
            $d = $resData[0];
            return isset($d->price) ? $d->price : 0;
        } else {
            return 0;
        }
    }
}