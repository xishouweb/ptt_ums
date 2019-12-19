<?php

namespace App\Console\Commands;

use App\Http\Controllers\App\ToolController;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PttMonitorTrading extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ptt:monitor_trading';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '根据etherscan提供接口监听ptt交易，若发现转账至我方钱包，则增加相应金额';

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
        $client = new Client();
        $response = $client->get('https://api.etherscan.io/api?module=account&action=tokentx&contractaddress=' . ToolController::PTT_ADDRESS . '&page=1&offset=100&sort=desc&apikey=' . ToolController::ETHERSCAN_API_KEY_TOKEN);
        $body = \GuzzleHttp\json_decode($response->getBody());
        foreach ($body as $data) {
            Log::info($data);
        }
    }
}
