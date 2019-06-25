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
            $data[$key] = $d->symbol;
        }

        DataCache::setSymbolsFor('binance', $data);
    }

    private function __setHuoBiSymbol()
    {
        $url = 'https://api.huobi.pro/v1/common/symbols';
        $client = new Client();
        $res = $client->request('GET', $url);
        $resData  = json_decode((string) $res->getBody());

        $data = [];
        foreach ($resData->data as $key => $d) {
            $data[$key] = $d->symbol;
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
        foreach ($info['btc-partition'] as $key => $d) {
            $data[] = $d['baseCurrency'] . $d['quoteCurrency'];
        }

        foreach ($info['trx-partition'] as $key => $d) {
             $data[] = $d['baseCurrency'] . $d['quoteCurrency'];
        }

        foreach ($info['usdt-partition'] as $key => $d) {
             $data[] = $d['baseCurrency'] . $d['quoteCurrency'];
        }

        foreach ($info['eth-partition'] as $key => $d) {
             $data[] = $d['baseCurrency'] . $d['quoteCurrency'];
        }

        DataCache::setSymbolsFor('cointiger', $data);
    }

    private function __setLbankSymbol()
    {
        $url = 'https://www.lbkex.net/v1/currencyPairs.do';
        $client = new Client();
        $res = $client->request('GET', $url);
        $resData  = json_decode((string) $res->getBody());
        DataCache::setSymbolsFor('lbank', $resData);
    }
}
