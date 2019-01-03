<?php
/**
 * Created by PhpStorm.
 * User: Kana
 * Date: 2018/9/13
 * Time: 16:41
 */

namespace App\Http\Controllers\App;


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
    const GET_PRICE_URL = 'https://api.coinmarketcap.com/v2/ticker/';
    const SEARCH_TOKEN = 'https://etherscan.io/searchHandler?term=';
    const CONTRACT_DETAIL = 'https://etherscan.io/token/';
    const OTHER_SEARCH_TOKEN = 'https://api.trustwalletapp.com/tokens/list?query=';

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

    //原版包一层
    public function searchToken()
    {
        $token_name = request()->input('name') ?: 'ETH';
        $response_data = DB::table('contracts')
            ->where('symbol', 'like', "%$token_name%")
            ->orWhere('name', 'like', "%$token_name%")
            ->select(['verified', 'enabled', '_id', 'address', 'symbol', 'decimals', 'totalSupply', 'name'])
            ->orderBy('id')
            ->get()
            ->toArray();

        if (!$response_data) {
            $http = new Client(['verify' => false]);
            $response = $http->get(self::OTHER_SEARCH_TOKEN . $token_name);
            $response_data = json_decode((string)$response->getBody(), true);
            foreach ($response_data as $data) {
                Contract::firstOrCreate([
                    'address'     => $data['address'],
                ], [
                    'verified'    => $data['verified'],
                    'enabled'     => $data['enabled'],
                    '_id'         => $data['_id'],
                    'address'     => $data['address'],
                    'symbol'      => $data['symbol'],
                    'decimals'    => $data['decimals'],
                    'totalSupply' => $data['totalSupply'],
                    'name'        => $data['name'],
                ]);
            }
        } else {
            foreach ($response_data as $key => $data) {
                if ($data->verified) {
                    $response_data[$key]->verified = true;
                }
                if ($data->enabled) {
                    $response_data[$key]->enabled = true;
                }
            }
        }

        return response()->json($response_data);
    }

//    todo 自己的搜索token接口
//    public function searchToken()
//    {
//        $http = new Client(['verify' => false]);
//        $response = $http->get(self::SEARCH_TOKEN . request()->input('name'));
//        $page_data = json_decode((string)$response->getBody(), true);
//        $response_data['data'] = [];
//        foreach ($page_data as $key => $item) {
//            $obj = new \stdClass();
//            if (strpos($item, '(') === false) {
//                unset($page_data[$key]);
//                continue;
//            } else if ($key > 4) {
//                break;
//            }
//            //简称
//            $obj->symbol = substr($item, strpos($item, '(') + 1, strpos($item, ')') - strpos($item, '(') - 1);
//            //全称
//            $obj->name = substr($item, 0, strpos($item, '(') - 1);
//            //合约地址
//            $obj->address = substr($item, strpos($item, '0x'), 42);
//            //去获取精度
//            $ql = new QueryList();
//            $ql = $ql->get(self::CONTRACT_DETAIL . $obj->address);
//            $obj->decimals = (int)$ql->find('#ContentPlaceHolder1_trDecimals td:eq(1)')->text();
//            //获取总量
//            //$obj->totalSupply = $ql->find('#ContentPlaceHolder1_trDecimals td:eq(1)')->text();
//            $response_data['data'][] = $obj;
//        }
//        return response()->json($response_data);
//    }

    /**
     * Send verify code to mobile phone
     *
     * @param Request $request
     * @return mixed
     */
    public function getCaptcha(Request $request)
    {
        $data['phone'] = $request->input('phone');
        $data['country'] = $request->input('country');

        $validator = Validator::make($data, [
            'phone' => 'required',
            'country' => 'required',
        ]);
        if ($validator->fails() || !(int)$data['country']) {
            return response()->json(['message' => '请输入正确的区号和手机号'], 403);
        }

        $redis = Redis::connection('default');
        $is_ok = $redis->set("messages-lock:" . $data['phone'], $_SERVER['REQUEST_TIME'], 'EX', 60, 'NX');
        if (!$is_ok) {
            return response()->json(['message' => '您提交频率过快，请60秒后再试'], 403);
        }

        $captcha = Captcha::send($data['phone'], (int)$data['country']);
        if ($captcha) {
            return response()->json(['message' => '验证码已发送']);
        }
        return response()->json(['message' => '发送失败，请重试'], 500);
    }

    public function latestVersion(Request $request)
    {
        if ($request->input('channel') == 'ios_enterprise') {
            $data = config('setting.ios_latest_version');
        } else {
            $data = config('setting.android_latest_version');
        }
        return response()->json($data);
    }

    public function getCryptoCurrencyPrice()
    {
        $symbols = ["PTT", "BTC", "ETH", "LTC", "BNB", "NEO", "QTUM", "EOS", "SNT", "BNT", "BCC", "GAS",  "OAX", "DNT", "MCO", "ICN", "WTC", "LRC", "OMG", "ZRX", "STRAT", "SNGLS", "KNC", "FUN", "SNM", "LINK", "XVG", "SALT", "MDA", "MTL", "SUB", "ETC", "MTH", "ENG", "ZEC", "AST", "DASH", "BTG", "EVX", "REQ", "VIB", "TRX", "POWR", "ARK", "XRP", "MOD", "ENJ", "STORJ", "VEN", "KMD", "NULS", "RCN", "RDN", "XMR", "DLT", "AMB", "BAT", "BCPT", "ARN", "GVT", "CDT", "GXS", "POE", "QSP", "BTS", "XZC", "LSK", "TNT", "FUEL", "MANA", "BCD", "DGD", "ADX", "ADA", "PPT", "CMT", "XLM", "CND", "LEND", "WABI", "TNB", "WAVES", "GTO", "ICX", "OST", "ELF", "AION", "NEBL", "BRD", "EDO", "WINGS", "NAV", "LUN", "TRIG", "APPC", "VIBE", "RLC", "INS", "PIVX", "IOST", "CHAT", "STEEM", "NANO", "VIA", "BLZ", "AE", "NCASH", "POA", "ZIL", "ONT", "STORM", "XEM", "WAN", "WPR", "QLC", "SYS", "GRS", "CLOAK", "GNT", "LOOM", "BCN", "REP", "TUSD", "ZEN", "SKY", "CVC", "THETA", "IOTX", "QKC", "AGI", "NXS", "DATA", "SC", "NPXS", "KEY", "NAS", "MFT", "DENT", "ARDR", "HOT", "VET", "DOCK", "POLY", "PHX", "HC", "GO", "PAX", "RVN", "DCR", "USDC", "MITH", "BCHABC", "REN",
        ];


        $priceData = DataCache::getSymbolsPrice();

        if (!$priceData) {
            $apiUrl = config('app.coinmarketcap_api_url');

            $symbolStr = implode(',', $symbols);
            $client = new Client();

            $headers = [
                'X-CMC_PRO_API_KEY' => config('app.coinmarketcap_api_key'),
                'json' => true,
                'gzip' => true,
            ];
            try {
                $res = $client->request('GET', $apiUrl . '?symbol=' . $symbolStr, ['headers' => $headers]);
                $resData  = json_decode((string) $res->getBody());

                if ($resData) {
                    $data = $resData->data;

                    foreach ($data as $k => $d) {
                        $temp['symbol'] = $k;
                        $temp['price'] = $d->quote->USD->price;

                        $priceData[] = $temp;
                    }

                    DataCache::setSymbolsPrice($priceData);
                }

                return $this->_success_json($priceData);
            } catch (\Exception $e) {

                \Log::error('coinmarketcap price get failed');
                \Log::error($e->getMessage());
                return $this->_bad_json('not found data of the symbol');
            }

        }
        return $this->_success_json($priceData);
    }

    private function __coinMarketCapDataFormat($data)
    {

    }
}