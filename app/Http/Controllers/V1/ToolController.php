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
        $symbols = DataCache::getSymbolsFrom('binance');
        if (!in_array($symbol, $symbols)) {
            return 0;
        }
        $url = 'https://api.binance.com/api/v3/ticker/price?symbol=';
        $client = new Client();
        $res = $client->request('GET', $url . $symbol);
        $resData  = json_decode((string) $res->getBody());

        return isset($resData->price) ? $resData->price : 0;
    }

    private function __getPriceFromHuoBi($symbol)
    {
        $symbols = DataCache::getSymbolsFrom('huobi');
        if (!in_array($symbol, $symbols)) {
            return 0;
        }
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
        $symbols = DataCache::getSymbolsFrom('cointiger');
        if (!in_array($symbol, $symbols)) {
            return 0;
        }

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
        $symbols = DataCache::getSymbolsFrom('lbank');
        if (!in_array($symbol, $symbols)) {
            return 0;
        }

        $url = 'https://www.lbkex.net/v1/trades.do?size=1&symbol=';
        $client = new Client();
        $res = $client->request('GET', $url . $symbol);
        $resData  = json_decode((string) $res->getBody());

        if (is_array($resData)) {
            $d = $resData[0];
            return isset($d->price) ? $d->price : 0;
        } else {
            return 0;
        }
    }

    public function get24DetailFor($symbol)
    {

        $count = 0;
        if ($lbankDetail = $this->__getDetailOfLbank($symbol)) {
            $count ++;
        }

        $symbol = str_replace('_', '', $symbol);

        if ($binanceDetail = $this->__getDetailOfbinance($symbol)) {
            $count ++;
        }

        if ($huoBiDetail = $this->__getDetailOfHuobi($symbol)) {
            $count ++;
        }

        if ($cointigerDetail = $this->__getDetailOfCointiger($symbol)) {
            $count ++;
        }

        return ($binanceDetail + $huoBiDetail + $cointigerDetail + $lbankDetail) / $count;
    }

    private function __getDetailOfCointiger($symbol)
    {
        $symbols = DataCache::getSymbolsFrom('cointiger');
        if (!in_array($symbol, $symbols)) {
            return 0;
        }

        $url = 'https://api.cointiger.com/exchange/trading/api/market/detail?symbol=';
        $client = new Client();
        $res = $client->request('GET', $url . $symbol);
        $resData  = json_decode((string) $res->getBody());

        if ($resData->code == '0') {
            return isset($resData->data->trade_ticker_data) ? $resData->data->trade_ticker_data->tick->rose : 0;
        } else {
            return 0;
        }
    }
    private function __getDetailOfbinance($symbol)
    {
        $symbol = strtoupper($symbol);
        $symbols = DataCache::getSymbolsFrom('binance');
        if (!in_array($symbol, $symbols)) {
            return 0;
        }

        $url = 'https://api.binance.com/api/v1/ticker/24hr?symbol=';
        $client = new Client();
        $res = $client->request('GET', $url . $symbol);
        $resData  = json_decode((string) $res->getBody());

        return isset($resData->priceChangePercent) ? $resData->priceChangePercent : 0;
    }

    private function __getDetailOfHuobi($symbol)
    {
        $symbols = DataCache::getSymbolsFrom('huobi');
        if (!in_array($symbol, $symbols)) {
            return 0;
        }
        $url='https://api.huobi.pro/market/detail?symbol=';
        $client = new Client();
        $res = $client->request('GET', $url . $symbol);
        $resData  = json_decode((string) $res->getBody());

        if ($resData->status == 'ok') {
            return isset($resData->tick) ?  round(($resData->tick->close - $resData->tick->open) / $resData->tick->open, 8) : 0;
        } else {
            return 0;
        }
    }

    private function __getDetailOfLbank($symbol)
    {
        $symbols = DataCache::getSymbolsFrom('lbank');
        if (!in_array($symbol, $symbols)) {
            return 0;
        }

        $url = 'https://www.lbkex.net/v1/ticker.do?symbol=';
        $client = new Client();
        $res = $client->request('GET', $url . $symbol);
        $resData  = json_decode((string) $res->getBody());

        return isset($resData->ticker->change) ? $resData->ticker->change : 0;
    }

    public function wechatMessageCallback(Request $request)
    {
        $appid = $request->get('appid');
        $timestamp = $request->get('timestamp');
        $data = $request->get('data');
        $sign = $request->get('sign');
        $key = '47886fd0de1asdf135sq22fy56w2kl';
        $secret = '1equEcRkT2hirJhbYByNGCZPRHgFg132rtlb0IZ3vf4=';

        $checkSign = md5($key . $secret . $timestamp . $data);

        if ($sign != $checkSign) {
           return response()->json([
                'resultcode' => -11001,
                'resultdesc' => 'invalid credentials',
                'data' => null,
           ], 200);
        }

        return response()->json([
                'resultcode' => 0,
                'resultdesc' => 'success',
                'data' => [
                    'nMsgType' => 2001,
                    'vcContent' => '币种: <ETH>
币价: ¥<2130.31543> / $<309.62988>
涨跌幅:
1H: <0.61>% <↑>
24H: <0.43>% <↑>
【<' . date('Y-m-d H:i:s') .  '>】
<https://proton.global>
',
                    'vcShareTitle' => null,
                    'vcShareDesc' => null,
                    'vcShareUrl' => null,

                ],
           ], 200);
    }
}