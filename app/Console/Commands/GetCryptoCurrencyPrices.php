<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

class GetCryptoCurrencyPrices extends Command
{

    const SYMBLOS = [
        "ETH",
        "LTC",
        "BNB",
        "NEO",
        "QTUM",
        "EOS",
        "SNT",
        "BNT",
        "BCC",
        "GAS",
        "BTC",
        "HSR",
        "OAX",
        "DNT",
        "MCO",
        "ICN",
        "WTC",
        "LRC",
        "YOYO",
        "OMG",
        "ZRX",
        "STRAT",
        "SNGLS",
        "BQX",
        "KNC",
        "FUN",
        "SNM",
        "IOTA",
        "LINK",
        "XVG",
        "SALT",
        "MDA",
        "MTL",
        "SUB",
        "ETC",
        "MTH",
        "ENG",
        "ZEC",
        "AST",
        "DASH",
        "BTG",
        "EVX",
        "REQ",
        "VIB",
        "TRX",
        "POWR",
        "ARK",
        "XRP",
        "MOD",
        "ENJ",
        "STORJ",
        "VEN",
        "KMD",
        "NULS",
        "RCN",
        "RDN",
        "XMR",
        "DLT",
        "AMB",
        "BAT",
        "BCPT",
        "ARN",
        "GVT",
        "CDT",
        "GXS",
        "POE",
        "QSP",
        "BTS",
        "XZC",
        "LSK",
        "TNT",
        "FUEL",
        "MANA",
        "BCD",
        "DGD",
        "ADX",
        "ADA",
        "PPT",
        "CMT",
        "XLM",
        "CND",
        "LEND",
        "WABI",
        "TNB",
        "WAVES",
        "GTO",
        "ICX",
        "OST",
        "ELF",
        "AION",
        "NEBL",
        "BRD",
        "EDO",
        "WINGS",
        "NAV",
        "LUN",
        "TRIG",
        "APPC",
        "VIBE",
        "RLC",
        "INS",
        "PIVX",
        "IOST",
        "CHAT",
        "STEEM",
        "NANO",
        "VIA",
        "BLZ",
        "AE",
        "RPX",
        "NCASH",
        "POA",
        "ZIL",
        "ONT",
        "STORM",
        "XEM",
        "WAN",
        "WPR",
        "QLC",
        "SYS",
        "GRS",
        "CLOAK",
        "GNT",
        "LOOM",
        "BCN",
        "REP",
        "TUSD",
        "ZEN",
        "SKY",
        "CVC",
        "THETA",
        "IOTX",
        "QKC",
        "AGI",
        "NXS",
        "DATA",
        "SC",
        "NPXS",
        "KEY",
        "NAS",
        "MFT",
        "DENT",
        "ARDR",
        "HOT",
        "VET",
        "DOCK",
        "POLY",
        "PHX",
        "HC",
        "GO",
        "PAX",
        "RVN",
        "DCR",
        "USDC",
        "MITH",
        "BCHABC",
        "BCHSV",
        "REN",
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token:getCryptoCurrencyPrices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get Crypto Currency Prices from coinmarketcap';

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

        $apiUrl = config('coinmarketcap_api_url');

        $symbolStr = implode(',', self::SYMBLOS);

        $client = new Client();

        $headers = [
            'X-CMC_PRO_API_KEY' => config('coinmarketcap_api_key'),
            'json' => true,
            'gzip' => true,
        ];

        $res = $client->request('GET', $apiUrl . '?symbol=' . $symbolStr, ['headers' => $headers]);
        $symbols  = json_decode((string) $res->getBody());
    }
}
