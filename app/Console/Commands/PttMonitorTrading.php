<?php

namespace App\Console\Commands;

use App\Http\Controllers\App\ToolController;
use App\Models\DataCache;
use App\Models\UserActionHistory;
use App\Models\UserWalletBalance;
use App\Models\UserWalletTransaction;
use GuzzleHttp\Client;
use App\Jobs\SendPtt;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\DispatchesJobs;

class PttMonitorTrading extends Command
{
    use DispatchesJobs;

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
        Log::info('监听ptt充提币');
        try {
            // 获取ptt交易list
            $client = new Client();
            $response = $client->get('https://api.etherscan.io/api?module=account&action=tokentx&contractaddress=' . ToolController::PTT_ADDRESS . '&page=1&offset=100&sort=desc&apikey=' . ToolController::ETHERSCAN_API_KEY_TOKEN);
            $body = \GuzzleHttp\json_decode($response->getBody());
            $result = $body->result;

            // 获取提币tx_hash
            $tx_hashes = UserWalletTransaction::where('type', UserWalletTransaction::OUT_TYPE)
                ->where('status', UserWalletTransaction::OUT_STATUS_TRANSFER)
                ->pluck('tx_hash')
                ->toArray();
        } catch (\Exception $e) {
            Log::error('监听ptt充币失败');
            Log::error($e->getMessage());
        }

        foreach ($result as $data) {
            try {
                // 判断是否为ptt转帐
                if ($data->contractAddress == ToolController::PTT_ADDRESS) {
                    // 充币
                    $user_wallet = UserWalletBalance::where('address', $data->to)->where('symbol', 'ptt')->first();
                    // 判断是否为我方钱包收款地址
                    if ($user_wallet) {
                        DB::beginTransaction();
                        $transaction = UserWalletTransaction::where('tx_hash', $data->hash)
                            ->where('type', UserWalletTransaction::IN_TYPE)
                            ->first();
                        // 判断记录是否存在
                        if ($transaction) {
                            // 判断区块确认是否大于15 && 判断记录的状态
                            if ($transaction->status == UserWalletTransaction::IN_STATUS_PADDING) {
                                if ($data->confirmations >= UserWalletTransaction::CONFIRM_COUNT) {

                                    // 修改transaction记录
                                    $transaction->status = UserWalletTransaction::IN_STATUS_SUCCESS;
                                    $transaction->block_confirm = $data->confirmations;
                                    $transaction->completed_at = date('Y-m-d H:i:s');
                                    $transaction->save();

                                    // 增加用户余额
                                    $user_wallet->total_balance += $transaction->amount;
                                    $user_wallet->save();

                                    // 记录行为
                                    UserActionHistory::record($user_wallet->user_id, UserActionHistory::TYPE_IN, $transaction->id);

                                    // 转币队列
                                    $this->dispatch((new SendPtt($transaction, 'receive'))->onQueue('send_ptt'));
                                } else {
                                    $transaction->block_confirm = $data->confirmations;
                                    $transaction->save();
                                }
                                Log::info($transaction);
                            }
                        } else {
                            $tran_data = [
                                'user_id' => $user_wallet->user_id,
                                'address' => $user_wallet->address,
                                'symbol' => UserWalletTransaction::PTT,
                                'type' => UserWalletTransaction::IN_TYPE,
                                'status' => UserWalletTransaction::IN_STATUS_PADDING,
                                'amount' => floor($data->value / 100000000000) / 10000000,
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

                    // 提币
                    if ($tx_hashes && in_array(strtolower($data->hash), $tx_hashes)) {
                        $transaction = UserWalletTransaction::where('tx_hash', $data->hash)
                            ->where('type', UserWalletTransaction::OUT_TYPE)
                            ->where('status', UserWalletTransaction::OUT_STATUS_TRANSFER)
                            ->first();
                        // 判断记录是否存在 判断记录的状态
                        if ($transaction && $data->confirmations > 0) {
                            DB::beginTransaction();
                            // 修改transaction
                            $transaction->status = UserWalletTransaction::OUT_STATUS_SUCCESS;
                            $transaction->block_number = $data->blockNumber;
                            $transaction->completed_at = date('Y-m-d H:i:s');
                            $transaction->save();
                            DB::commit();
                            Log::info($transaction);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('监听ptt充币失败foreach');
                Log::error($e->getMessage());
                Log::error(json_encode($data));
                DB::rollBack();
            }
        }
        Log::info('监听ptt充提币完毕');
    }
}
