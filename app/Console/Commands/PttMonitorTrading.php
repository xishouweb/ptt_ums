<?php

namespace App\Console\Commands;

use App\Http\Controllers\App\ToolController;
use App\Models\DataCache;
use App\Models\UserWalletBalance;
use App\Models\UserWalletTransaction;
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
        Log::info('start');
        $client = new Client();
        $response = $client->get('https://api.etherscan.io/api?module=account&action=tokentx&contractaddress=' . ToolController::PTT_ADDRESS . '&page=1&offset=100&sort=desc&apikey=' . ToolController::ETHERSCAN_API_KEY_TOKEN);
//        $test = '{"status":"1","message":"OK","result":[{"blockNumber":"9129731","timeStamp":"1576743032","hash":"0xaf7af16f93e834c5c9b99e01b84de8c5fb02631e45cbb4d8137a6aeb68fa312c","nonce":"2","blockHash":"0x1c591ae2427ccf82524b4ae80bbd20b7bc84885857e0d41d0bce1e3ebf1401a0","from":"0x8a022353326660a600cc6ea49530857e733926b9","contractAddress":"0x4689a4e169eb39cc9078c0940e21ff1aa8a39b9c","to":"0x0211f3cedbef3143223d3acf0e589747933e8527","value":"952342000000000000000000","tokenName":"Proton Token","tokenSymbol":"PTT","tokenDecimal":"18","transactionIndex":"180","gas":"40850","gasPrice":"9099999538","gasUsed":"25850","cumulativeGasUsed":"7458544","input":"deprecated","confirmations":"25"},{"blockNumber":"9129722","timeStamp":"1576742682","hash":"0x4eb567186551107e6507926adead2638c3941269b86d387fa7120c546e7726ab","nonce":"120262","blockHash":"0x30a84a8cefc69c9efa3461e4d53a68801f37a213f530f1af82959ddcdc92b2b4","from":"0x94623b4bd510be5aac9873546ad4c1b8218e22b4","contractAddress":"0x4689a4e169eb39cc9078c0940e21ff1aa8a39b9c","to":"0x8a022353326660a600cc6ea49530857e733926b9","value":"952342000000000000000000","tokenName":"Proton Token","tokenSymbol":"PTT","tokenDecimal":"18","transactionIndex":"41","gas":"60000","gasPrice":"40000000000","gasUsed":"55838","cumulativeGasUsed":"1143433","input":"deprecated","confirmations":"34"}]}';
        $body = \GuzzleHttp\json_decode($response->getBody());
//        $body = json_decode($test);
//        Log::info($body);
        $last_confirm_tx_hash = DataCache::getPttLastConfirmTxHash();
        $result = $body->result;
        foreach ($result as $data) {
            // 判断上次最后确认的tx_hash，减少foreach次数
            if ($last_confirm_tx_hash == $data->hash) {
                break;
            }
            // 判断是否为ptt转帐
            if ($data->contractAddress == ToolController::PTT_ADDRESS) {
                $user_wallet = UserWalletBalance::where('address', $data->to)->first();
                // 判断是否为我方钱包收款地址
                if ($user_wallet) {
                    $transaction = UserWalletTransaction::where('tx_hash', $data->hash)->first();
                    // 判断记录是否存在
                    if ($transaction) {
                        // 判断区块确认是否大于15
                        if ($data->confirmations >= UserWalletTransaction::CONFIRM_COUNT) {
                            // 判断记录的状态
                            if (UserWalletTransaction::IN_TYPE && UserWalletTransaction::IN_STATUS_PADDING) {
                                $data->status = UserWalletTransaction::IN_STATUS_SUCCESS;
                                $data->block_confirm = $data->confirmations;
                                $data->save();
                            } else {
                                DataCache::setPttLastConfirmTxHash($data->hash);
                            }
                        } else {
                            $data->block_confirm = $data->confirmations;
                            $data->save();
                        }
                        Log::info($transaction);
                    } else {
                        $tran_data = [
                            'user_id' => $user_wallet->user_id,
                            'address' => $user_wallet->address,
                            'symbol' => UserWalletTransaction::PTT,
                            'type' => UserWalletTransaction::IN_TYPE,
                            'status' => UserWalletTransaction::IN_STATUS_PADDING,
                            'amount' => $data->value,
                            'to' => $data->to,
                            'from' => $data->from,
                            'fee' => $data->gasUsed,
                            'tx_hash' => $data->hash,
                            'block_number' => $data->blockNumber,
                            'block_confirm' => $data->confirmations,
                        ];
                        $tran = UserWalletTransaction::create($tran_data);
                        Log::info($tran);
                    }
                }
            }
            Log::info($data->hash);
        }
        Log::info('finish');
    }
}
