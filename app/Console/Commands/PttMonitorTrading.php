<?php

namespace App\Console\Commands;

use App\Http\Controllers\App\ToolController;
use App\Models\DataCache;
use App\Models\UserWalletBalance;
use App\Models\UserWalletTransaction;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
        Log::info('监听ptt充币');
        try {
            $client = new Client();
            $response = $client->get('https://api.etherscan.io/api?module=account&action=tokentx&contractaddress=' . ToolController::PTT_ADDRESS . '&page=1&offset=100&sort=desc&apikey=' . ToolController::ETHERSCAN_API_KEY_TOKEN);
            $body = \GuzzleHttp\json_decode($response->getBody());
            $last_confirm_tx_hash = DataCache::getPttLastConfirmTxHash();
            $result = $body->result;
        } catch (\Exception $e) {
            Log::error('监听ptt充币失败');
            Log::error($e->getMessage());
        }

        foreach ($result as $data) {
            try {
                // 判断上次最后确认的tx_hash，减少foreach次数
                if ($last_confirm_tx_hash == $data->hash) {
                    break;
                }
                // 判断是否为ptt转帐
                if ($data->contractAddress == ToolController::PTT_ADDRESS) {
                    $user_wallet = UserWalletBalance::where('address', $data->to)->where('symbol', 'ptt')->first();
                    // 判断是否为我方钱包收款地址
                    if ($user_wallet) {
                        DB::beginTransaction();
                        $transaction = UserWalletTransaction::where('tx_hash', $data->hash)->first();
                        // 判断记录是否存在
                        if ($transaction) {
                            // 判断区块确认是否大于15
                            if ($data->confirmations >= UserWalletTransaction::CONFIRM_COUNT) {
                                // 判断记录的状态
                                if ($transaction->type == UserWalletTransaction::IN_TYPE && $transaction->status == UserWalletTransaction::IN_STATUS_PADDING) {
                                    $transaction->status = UserWalletTransaction::IN_STATUS_SUCCESS;
                                    $transaction->block_confirm = $data->confirmations;
                                    $transaction->completed_at = date('Y-m-d H:i:s');
                                    $transaction->save();
                                    $user_wallet->total_balance += $transaction->amount;
                                    $user_wallet->save();
                                } else {
                                    DataCache::setPttLastConfirmTxHash($data->hash);
                                }
                            } else {
                                $transaction->block_confirm = $data->confirmations;
                                $transaction->save();
                            }
                            Log::info($transaction);
                        } else {
                            $tran_data = [
                                'user_id' => $user_wallet->user_id,
                                'address' => $user_wallet->address,
                                'symbol' => UserWalletTransaction::PTT,
                                'type' => UserWalletTransaction::IN_TYPE,
                                'status' => UserWalletTransaction::IN_STATUS_PADDING,
                                'amount' => round($data->value / UserWalletTransaction::DIGIT,8),
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
                        DB::commit();
                    }
                }
            } catch (\Exception $e) {
                Log::error('监听ptt充币失败foreach');
                Log::error($e->getMessage());
                Log::error(json_encode($data));
                DB::rollBack();
            }
        }
    }
}
