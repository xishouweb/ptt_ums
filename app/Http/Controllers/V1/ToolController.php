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
    const JIA_QUN_LA_APPID = 'BJBTK-1000001';
    const JIA_QUN_LA_SECRET = 'BJBTK-8b672617101c99eb884f30606366e53a';

    public function getPrice($symbol)
    {
        $count = 0;
        if ($lbankPrice = $this->__getPriceFromLbank($symbol)) {
            $count ++;
        }

        if ($binancePrice = $this->__getPriceFromBinance($symbol)) {
            $count ++;
        }

        if ($huoBiPrice = $this->__getPriceFromHuoBi($symbol)) {
            $count ++;
        }

        if ($cointigerPrice = $this->__getPriceFromCointiger($symbol)) {
            $count ++;
        }

        return round(($binancePrice + $huoBiPrice + $cointigerPrice + $lbankPrice) / $count, 4);
    }

    private function __getPriceFromBinance($symbol, $is_check = true)
    {
        $symbol = strtoupper($symbol);
        $basePrice = 1;
        if ($is_check) {
            if (!DataCache::getSymbols('symbol_binance_ETH_' . $symbol)) {
                if (!DataCache::getSymbols('symbol_binance_BTC_' . $symbol)) {
                    if (!DataCache::getSymbols('symbol_binance_USDT_' . $symbol)) {
                        if (!DataCache::getSymbols('symbol_binance_BNB_' . $symbol)) {
                            return 0;
                        } else {
                            $symbol .= 'BNB';
                            $basePrice = $this->__getBasePrice('bnb');
                        }
                    }else {
                        $symbol .= 'USDT';
                    }
                } else {
                    $symbol .= 'BTC';
                    $basePrice = $this->__getBasePrice('btc');
                }
            } else {
                $symbol .= 'ETH';
                $basePrice = $this->__getBasePrice('eth');
            }
        }

        \Log::info('binance price symbol = '. $symbol);
        $url = 'https://api.binance.com/api/v3/ticker/price?symbol=';
        $client = new Client();
        $res = $client->request('GET', $url . $symbol);
        $resData  = json_decode((string) $res->getBody());

        return isset($resData->price) ? $resData->price * $basePrice : 0;
    }

    private function __getBasePrice($symbol)
    {
        if ($price = DataCache::getBaseSymbolPrice($symbol)) {
            return $price;
        }

        switch ($symbol) {
            case 'eth':
                $price = $this->__getPriceFromBinance("ETHUSDT", false);
                break;
            case 'btc':
                $price = $this->__getPriceFromBinance("BTCUSDT", false);
                break;
            case 'bnb':
                $price = $this->__getPriceFromBinance("BNBUSDT", false);
                break;
            case 'ht':
                $price = $this->__getPriceFromHuoBi("htusdt", false);
                break;
        }

        DataCache::setBaseSymbolsPrice($symbol, $price);
        return $price;
    }

    private function __getPriceFromHuoBi($symbol, $is_check = true)
    {
        $basePrice = 1;
        if ($is_check) {
            if (!DataCache::getSymbols('symbol_huobi_eth_' . $symbol)) {
                if (!DataCache::getSymbols('symbol_huobi_btc_' . $symbol)) {
                    if (!DataCache::getSymbols('symbol_huobi_usdt_' . $symbol)) {
                        if (!DataCache::getSymbols('symbol_huobi_ht_' . $symbol)) {
                            return 0;
                        } else {
                            $symbol .= 'ht';
                            $basePrice = $this->__getBasePrice('ht');
                        }
                    }else {
                        $symbol .= 'usdt';
                    }
                } else {
                    $symbol .= 'btc';
                    $basePrice = $this->__getBasePrice('btc');
                }
            } else {
                $symbol .= 'eth';
                $basePrice = $this->__getBasePrice('eth');
            }
        }

     \Log::info('huobi price symbol = '. $symbol);
        $url='https://api.huobi.pro/market/trade?symbol=';
        $client = new Client();
        $res = $client->request('GET', $url . $symbol);
        $resData  = json_decode((string) $res->getBody());

        if ($resData->status == 'ok') {
            return isset($resData->tick) && isset($resData->tick->data) ? $resData->tick->data[0]->price * $basePrice : 0;
        } else {
            return 0;
        }
        return isset($resData->price) ? $resData->price : 0;
    }

    private function __getPriceFromCointiger($symbol)
    {
        $basePrice = 1;
        if (!DataCache::getSymbols('symbol_cointiger_eth_' . $symbol)) {
            if (!DataCache::getSymbols('symbol_cointiger_btc_' . $symbol)) {
                if (!DataCache::getSymbols('symbol_cointiger_usdt_' . $symbol)) {
                    return 0;
                }else {
                    $symbol .= 'usdt';
                }
            } else {
                $symbol .= 'btc';
                $basePrice = $this->__getBasePrice('btc');
            }
        } else {
            $symbol .= 'eth';
            $basePrice = $this->__getBasePrice('eth');
        }

        \Log::info('cointiger price symbol = '. $symbol);
        $url = 'https://api.cointiger.com/exchange/trading/api/market/history/trade?symbol=';
        $client = new Client();
        $res = $client->request('GET', $url . $symbol);
        $resData  = json_decode((string) $res->getBody());

        if ($resData->code == '0') {
            return isset($resData->data) && isset($resData->data->trade_data) ? $resData->data->trade_data[0]->price * $basePrice : 0;
        } else {
            return 0;
        }
    }

    private function __getPriceFromLbank($symbol)
    {
        $basePrice = 1;
        if (!DataCache::getSymbols('symbol_lbank_eth_' . $symbol)) {
            if (!DataCache::getSymbols('symbol_lbank_btc_' . $symbol)) {
                if (!DataCache::getSymbols('symbol_lbank_usdt_' . $symbol)) {
                    return 0;
                }else {
                    $symbol .= 'usdt';
                }
            } else {
                $symbol .= 'btc';
                $basePrice = $this->__getBasePrice('btc');
            }
        } else {
            $symbol .= 'eth';
            $basePrice = $this->__getBasePrice('eth');
        }
       \Log::info('lbank price symbol = '. $symbol);
        $url = 'https://www.lbkex.net/v1/trades.do?size=1&symbol=';
        $client = new Client();
        $res = $client->request('GET', $url . $symbol);
        $resData  = json_decode((string) $res->getBody());

        if (is_array($resData)) {
            $d = $resData[0];
            return isset($d->price) ? $d->price * $basePrice : 0;
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

        return round(($binanceDetail + $huoBiDetail + $cointigerDetail + $lbankDetail) / $count, 4);
    }

    private function __getDetailOfCointiger($symbol)
    {
        if (!DataCache::getSymbols('symbol_cointiger_eth_' . $symbol)) {
            if (!DataCache::getSymbols('symbol_cointiger_btc_' . $symbol)) {
                if (!DataCache::getSymbols('symbol_cointiger_usdt_' . $symbol)) {
                    return 0;
                }else {
                    $symbol .= 'usdt';
                }
            } else {
                $symbol .= 'btc';
            }
        } else {
            $symbol .= 'eth';
        }

        \Log::info('cointiger rose symbol = '. $symbol);

        $url = 'https://api.cointiger.com/exchange/trading/api/market/detail?symbol=';
        $client = new Client();
        $res = $client->request('GET', $url . $symbol);
        $resData  = json_decode((string) $res->getBody());

        if ($resData->code == '0') {
            return isset($resData->data->trade_ticker_data) ? round($resData->data->trade_ticker_data->tick->rose, 4) * 100 : 0;
        } else {
            return 0;
        }
    }
    private function __getDetailOfbinance($symbol)
    {
        $symbol = strtoupper($symbol);
        if (!DataCache::getSymbols('symbol_binance_ETH_' . $symbol)) {
            if (!DataCache::getSymbols('symbol_binance_BTC_' . $symbol)) {
                if (!DataCache::getSymbols('symbol_binance_USDT_' . $symbol)) {
                    if (!DataCache::getSymbols('symbol_binance_BNB_' . $symbol)) {
                        return 0;
                    } else {
                        $symbol .= 'BNB';
                    }
                }else {
                    $symbol .= 'USDT';
                }
            } else {
                $symbol .= 'BTC';
            }
        } else {
            $symbol .= 'ETH';
        }

\Log::info('binance rose symbol = '. $symbol);
        $url = 'https://api.binance.com/api/v1/ticker/24hr?symbol=';
        $client = new Client();
        $res = $client->request('GET', $url . $symbol);
        $resData  = json_decode((string) $res->getBody());

        return isset($resData->priceChangePercent) ?  $resData->priceChangePercent : 0;
    }

    private function __getDetailOfHuobi($symbol)
    {
        if (!DataCache::getSymbols('symbol_huobi_eth_' . $symbol)) {
            if (!DataCache::getSymbols('symbol_huobi_btc_' . $symbol)) {
                if (!DataCache::getSymbols('symbol_huobi_usdt_' . $symbol)) {
                    if (!DataCache::getSymbols('symbol_huobi_ht_' . $symbol)) {
                        return 0;
                    } else {
                        $symbol .= 'ht';
                    }
                }else {
                    $symbol .= 'usdt';
                }
            } else {
                $symbol .= 'btc';
            }
        } else {
            $symbol .= 'eth';
        }

     \Log::info('huobi rose symbol = '. $symbol);
        $url='https://api.huobi.pro/market/detail?symbol=';
        $client = new Client();
        $res = $client->request('GET', $url . $symbol);
        $resData  = json_decode((string) $res->getBody());

        if ($resData->status == 'ok') {
            return isset($resData->tick) ?  round(($resData->tick->close - $resData->tick->open) / $resData->tick->open, 4) * 100 : 0;
        } else {
            return 0;
        }
    }

    private function __getDetailOfLbank($symbol)
    {
        if (!DataCache::getSymbols('symbol_lbank_eth_' . $symbol)) {
            if (!DataCache::getSymbols('symbol_lbank_btc_' . $symbol)) {
                if (!DataCache::getSymbols('symbol_lbank_usdt_' . $symbol)) {
                    return 0;
                }else {
                    $symbol .= 'usdt';
                }
            } else {
                $symbol .= 'btc';
            }
        } else {
            $symbol .= 'eth';
        }
       \Log::info('lbank rose symbol = '. $symbol);

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

        $checkSign = md5($appid . $secret . $timestamp . $data);

        if ($sign != $checkSign) {
           return response()->json([
                'resultcode' => -11001,
                'resultdesc' => 'invalid credentials',
                'data' => null,
           ], 200);
        }

        $data = json_decode(base64_decode($data));
        $symbol = strtolower($data->vcKeyword);
        if ($d = DataCache::getSymbolInfo('symbol-info-data-' . $symbol)) {
            $price = $d['price'];
            $rose = $d['rose'];
        } else {
            if (!DataCache::lock('symbol-info-lock', 5)) {
                return response()->json([
                    'resultcode' => 0,
                    'resultdesc' => 'success',
                    'data' => [
                        'nMsgType' => 2001,
                        'vcContent' => '有点累了,我让歇5秒😛
【' . date('Y-m-d H:i:s') .  '】
https://proton.global
',
                        'vcShareTitle' => null,
                        'vcShareDesc' => null,
                        'vcShareUrl' => null,

                    ],
               ], 200);
            }
            $price = $this->getPrice($symbol);
            $rose = $this->get24DetailFor($symbol);
            DataCache::setSymbolInfo('symbol-info-data-' . $symbol, ['price' => $price, 'rose' => $rose]);
        }
        $cny = DataCache::getCurrency('cny');
        return response()->json([
                'resultcode' => 0,
                'resultdesc' => 'success',
                'data' => [
                    'nMsgType' => 2001,
                    'vcContent' => '币种: ' . $data->vcKeyword .'
币价: ¥' . $price * $cny .' / $ ' . $price . '
涨跌幅:
24H: ' . $rose .  '% ' . ($rose > 0 ? '↑' : '↓' ) . '
【' . date('Y-m-d H:i:s') .  '】
https://proton.global
',
                    'vcShareTitle' => null,
                    'vcShareDesc' => null,
                    'vcShareUrl' => null,

                ],
           ], 200);
    }

    public function setKeyword()
    {
        $symbols = DataCache::getSymbols('keywords-symbol');

        $data = base64_encode(json_encode([
            'nActivityId' => 10158,
            'vcName' => 10158,
            'vcKeyword' => $symbols
        ]));

        $timestamp = time();

        $sign = md5(static::JIA_QUN_LA_APPID . static::JIA_QUN_LA_SECRET . $timestamp . $data);

        $url = 'http://en.hytest.jinqunla.com/api/External/keyword/SetkeywordsImport';
        $body =  json_encode([
                   'appid' => static::JIA_QUN_LA_APPID,
                   'sign' => $sign,
                   'timestamp' => $timestamp,
                   'data' => $data,
               ]);

        $client = new Client();
        $res = $client->request('POST', $url,  [
            'body' => $body,
            'headers' => ['Content-Type' => 'application/json']
           ]);
        $resData  = json_decode((string) $res->getBody(), true);
        return $resData;
    }
}