<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use App\Models\DataCache;

class GetExchangesSymbols extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token:getSymbolsFromExchanges';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取交易所支持的交易对';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $keywords = [];
        $this->__setBinanceSymbol($keywords);
        $this->__setHuoBiSymbol($keywords);
        $this->__setCointigerSymbol($keywords);
        $this->__setLbankSymbol($keywords);

        DataCache::setSymbolsFor('keywords-symbol', $keywords);
    }

    private function __setBinanceSymbol(&$keywords)
    {
        $url = 'https://api.binance.com/api/v1/exchangeInfo';
        $client = new Client();
        $res = $client->request('GET', $url);
        $resData  = json_decode((string) $res->getBody());

        $data = [];
        foreach ($resData->symbols as $d) {
            DataCache::setSymbolsFor('symbol_binance_' . $d->quoteAsset . '_' . $d->baseAsset, 1);
            $keyword = strtolower($d->baseAsset);
            if (!in_array($keyword, $keywords)) {
                $keywords[] = $keyword;
            }
        }
    }

    private function __setHuoBiSymbol(&$keywords)
    {
        $url = 'https://api.huobi.pro/v1/common/symbols';
        $client = new Client();
        $res = $client->request('GET', $url);
        $resData  = json_decode((string) $res->getBody(), true);

        $data = [];
        foreach ($resData['data'] as $d) {
            DataCache::setSymbolsFor('symbol_huobi_' . $d['quote-currency'] . '_' . $d['base-currency'], 1);
            $keyword = strtolower($d['base-currency']);
            if (!in_array($keyword, $keywords)) {
                $keywords[] = $keyword;
            }
        }
    }

    private function __setCointigerSymbol(&$keywords)
    {
        $url = 'https://api.cointiger.com/exchange/trading/api/v2/currencys/v2';
        $client = new Client();
        $res = $client->request('GET', $url);
        $resData  = json_decode((string) $res->getBody(), true);
        $info = $resData['data'];

        $data = [];

        foreach ($info as $key => $value) {
            $arr = explode('-', $key);
            foreach ($info[$key] as $d) {
                DataCache::setSymbolsFor('symbol_cointiger_' .$arr[0] . '_' . $d['baseCurrency'], 1);
                $keyword = strtolower($d['baseCurrency']);
                if (!in_array($keyword, $keywords)) {
                    $keywords[] = $keyword;
                }
            }
        }
    }

    private function __setLbankSymbol(&$keywords)
    {
        $url = 'https://www.lbkex.net/v1/currencyPairs.do';
        $client = new Client();
        $res = $client->request('GET', $url);
        $resData  = json_decode((string) $res->getBody());
        $data = [];
        foreach ($resData as $value) {
            $arr = explode('_', $value);
            $data[$arr[1]][] = $arr[0];
            DataCache::setSymbolsFor('symbol_lbank_' .$arr[1] . '_' . $arr[0], 1);
            $keyword = strtolower($arr[0]);
            if (!in_array($keyword, $keywords)) {
                $keywords[] = $keyword;
            }
        }
    }
}
