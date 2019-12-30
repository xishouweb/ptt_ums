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

class PttMonitorTradingTxHash extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ptt:monitor_trading_tx_hash';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '根据tx_hash，etherscan提供接口监听ptt交易';

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
        Log::info('监听ptt提币');
        $tx_hashes = UserWalletTransaction::where('type', UserWalletTransaction::OUT_TYPE)
            ->where('status', UserWalletTransaction::OUT_STATUS_TRANSFER)
            ->pluck('tx_hash')
            ->toArray();
        if ($tx_hashes) {
            Log::info($tx_hashes);
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
                    if (in_array(strtolower($data->hash), $tx_hashes)) {
                        $transaction = UserWalletTransaction::where('tx_hash', $data->hash)->first();
                        // 判断记录是否存在 判断记录的状态
                        if ($transaction && $data->confirmations >= 0 && $transaction->status == UserWalletTransaction::OUT_STATUS_TRANSFER) {
                            DB::beginTransaction();
                            $transaction->status = UserWalletTransaction::OUT_STATUS_SUCCESS;
                            $transaction->block_number = $data->blockNumber;
                            $transaction->completed_at = date('Y-m-d H:i:s');
                            $transaction->save();
                            $user_wallet = UserWalletBalance::where('user_id', $transaction->user_id)->first();
                            $amount = round($transaction->amount, 8);
                            $user_wallet->locked_balance -= $amount;
                            $user_wallet->total_balance -= $amount;
                            $user_wallet->save();
                            DB::commit();
                            Log::info($transaction);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('监听ptt交易失败foreach');
                    Log::error($e->getMessage());
                    Log::error(json_encode($data));
                    DB::rollBack();
                }
            }
        }
    }
}
