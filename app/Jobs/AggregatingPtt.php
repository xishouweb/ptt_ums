<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Services\PttCloudAcount;
use App\Models\UserWallet;
use App\Models\TransactionActionHistory;

class AggregatingPtt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tx;
    protected $type;

    const TRANSFOR_LIMIT = 1;
    const GAS_limit = 60000;
    const DECIMALS = 1000000000000000000;

    public $timeout = 600;

    public function __construct($tx, $type)
	{
	    $this->tx = $tx;
	    $this->type = $type;
    }

    public function handle()
	{
        try {
            $tx = $this->tx;

            $wallet = UserWallet::whereUserId($tx->user_id)->whereAddress($tx->address)->first();
            if (!$wallet) {
                \Log::error('未找到该用户钱包信息 ===> ', [$tx]);
                return;
            }
            $gasPrice = PttCloudAcount::getGasPrice();
            if ($this->type = 'receive') {
                $ptt_balance = PttCloudAcount::getBalance($tx->address, 'ptt');
                \Log::info('ptt 余额 ====> ' . $ptt_balance);
                if ($ptt_balance < self::TRANSFOR_LIMIT * self::DECIMALS) return;

                $eth_balance = PttCloudAcount::getBalance($tx->address);
                \Log::info('eth 余额 ====> ' . $eth_balance);

                if ($eth_balance >= self::GAS_limit * $gasPrice) {
                    $record = PttCloudAcount::sendTransaction(config('app.ptt_offline_address'), bcmul((string)$tx->amount, (string)self::DECIMALS), 'ptt', [
                        'from' => $tx->address,
                        'keystore' => $wallet->key_store,
                        'password' => decrypt($wallet->password),
                        'ums_tx_id' => $tx->id,
                    ]);

                    $ac = TransactionActionHistory::whereTxId($tx->id)
                        ->whereStatus(TransactionActionHistory::STATUS_PADDING)
                        ->first();
                    if (!$ac) {
                        TransactionActionHistory::create([
                            'user_id' => $tx->user_id,
                            'symbol' => 'ptt',
                            'amount' => $tx->amount,
                            'status' => TransactionActionHistory::STATUS_PADDING,
                            'type' => 'receive',
                            'to' => config('app.ptt_offline_address'),
                            'from' => $tx->address,
                            'tx_id' => $tx->id,
                        ]);
                    }
                    \Log::info('转账详情tx_id: '. $tx->id .' ======> ', [$record]);
                } else {
                    $record = PttCloudAcount::sendTransaction($tx->address, bcmul((string)self::GAS_limit, (string)$gasPrice));
                    \Log::info('gsa转账详情 '. $tx->id .' ======> ', [$record]);
                    TransactionActionHistory::create([
                        'user_id' => $tx->user_id,
                        'symbol' => 'eth',
                        'amount' => self::GAS_limit * $gasPrice / self::DECIMALS,
                        'status' => TransactionActionHistory::STATUS_SUSSESS,
                        'type' => 'gas',
                        'to' => $tx->address,
                        'from' => config('app.ptt_master_address'),
                        'tx_id' => $tx->id,
                    ]);

                    $this->release(3 * 60);
                }
            }
        } catch (\Exception $e) {
            \Log::error('队列汇聚ptt失败 ===> ', [$e->getMessage()]);
            TransactionActionHistory::create([
                'user_id' => $tx->user_id,
                'symbol' => 'ptt',
                'amount' => $tx->amount,
                'status' => TransactionActionHistory::STATUS_FAILED,
                'type' => 'receive',
                'from' => $tx->address,
                'tx_id' => $tx->id,
            ]);
        }
    }
}