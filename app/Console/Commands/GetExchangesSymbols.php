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
        $this->__setBinanceSymbol();
        $this->__setHuoBiSymbol();
        $this->__setCointigerSymbol();
        $this->__setLbankSymbol();
    }

    private function __setBinanceSymbol()
    {
        $url = 'https://api.binance.com/api/v1/exchangeInfo';
        $client = new Client();
        $res = $client->request('GET', $url);
        $resData  = json_decode((string) $res->getBody());

        $data = [];
        foreach ($resData->symbols as $key => $d) {
            $data[$d->quoteAsset][] = $d->baseAsset;
        }

        DataCache::setSymbolsFor('binance', $data);
    }

    private function __setHuoBiSymbol()
    {
        $url = 'https://api.huobi.pro/v1/common/symbols';
        $client = new Client();
        $res = $client->request('GET', $url);
        $resData  = json_decode((string) $res->getBody(), true);

        $data = [];
        foreach ($resData['data'] as $d) {
            $data[$d['quote-currency']][] = $d['base-currency'];
        }

        DataCache::setSymbolsFor('huobi', $data);
    }

    private function __setCointigerSymbol()
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
                $data[$arr[0]][] = $d['baseCurrency'];
            }
        }

        DataCache::setSymbolsFor('cointiger', $data);
    }

    private function __setLbankSymbol()
    {
        $url = 'https://www.lbkex.net/v1/currencyPairs.do';
        $client = new Client();
        $res = $client->request('GET', $url);
        $resData  = json_decode((string) $res->getBody());
        $data = [];
        foreach ($resData as $value) {
            $arr = explode('_', $value);
            $data[$arr[1]][] = $arr[0];
        }
        DataCache::setSymbolsFor('lbank', $data);
    }
}
