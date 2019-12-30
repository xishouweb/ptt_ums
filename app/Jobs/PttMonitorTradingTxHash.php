<?php

namespace App\Jobs;

use App\Http\Controllers\App\ToolController;
use App\Models\UserWalletBalance;
use App\Models\UserWalletTransaction;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PttMonitorTradingTxHash implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tx_hash;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tx_hash)
    {
        $this->tx_hash = strtolower($tx_hash);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $completed = false;
        $i = 1;
        while (!$completed) {
            Log::info('监听ptt交易，第' . $i . '次，tx_hash = ' . $this->tx_hash);
            try {
                $client = new Client();
                $response = $client->get('https://api.etherscan.io/api?module=account&action=tokentx&contractaddress=' . ToolController::PTT_ADDRESS . '&page=1&offset=100&sort=desc&apikey=' . ToolController::ETHERSCAN_API_KEY_TOKEN);
                $body = \GuzzleHttp\json_decode($response->getBody());
                $result = $body->result;
            } catch (\Exception $e) {
                Log::error('监听ptt交易失败');
                Log::error($e->getMessage());
            }

            foreach ($result as $data) {
                try {
                    if ($this->tx_hash == strtolower($data->hash)) {
                        $transaction = UserWalletTransaction::where('tx_hash', $data->hash)->where('type', UserWalletTransaction::OUT_TYPE)->first();
                        // 判断记录是否存在
                        if ($transaction && $data->confirmations >= 0) {
                            // 判断记录的状态
                            if ($transaction->status == UserWalletTransaction::OUT_STATUS_TRANSFER) {
                                DB::beginTransaction();
                                $transaction->status = UserWalletTransaction::OUT_STATUS_SUCCESS;
                                $transaction->block_number = $data->blockNumber;
                                $transaction->completed_at = date('Y-m-d H:i:s');
                                $transaction->save();
                                $user_wallet = UserWalletBalance::where('address', $data->to)->first();
                                $amount = round($transaction->amount, 8);
                                $user_wallet->locked_balance -= $amount;
                                $user_wallet->total_balance -= $amount;
                                $user_wallet->save();
                                DB::commit();
                                $completed = true;
                            }
                            Log::info($transaction);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('监听ptt交易失败foreach');
                    Log::error($e->getMessage());
                    Log::error($data);
                    DB::rollBack();
                }
            }
            sleep(60);
            $i++;
        }

    }
}
