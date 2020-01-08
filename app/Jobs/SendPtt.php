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

class SendPtt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tx;
    protected $type;
    
    const TRANSFOR_LIMIT = 1;
    const GAS_limit = 60000;
    const DECIMALS = 1000000000000000000;

    public $timeout = 180;

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
                $ptt_balance = floor($ptt_balance / self::DECIMALS);
                \Log::info('ptt 余额 ====> ' . $ptt_balance);
                if ($ptt_balance < self::TRANSFOR_LIMIT ) return;

                $eth_balance = PttCloudAcount::getBalance($tx->address);
                \Log::info('eth 余额 ====> ' . $eth_balance);

                if ($eth_balance >= self::GAS_limit * $gasPrice) {
                    $record = PttCloudAcount::sendTransaction(config('app.ptt_master_address'), $ptt_balance, $gasPrice, 'ptt', [
                        'from' => $tx->address,
                        'keystore' => $wallet->key_store,
                        'password' => decrypt($wallet->password),
                    ]);
                    TransactionActionHistory::create([
                        'user_id' => $x->user_id,
                        'symbol' => 'ptt',
                        'amount' => $tx->amount,
                        'status' => TransactionActionHistory::STATUS_SUSSESS,
                        'type' => 'receive',
                        'to' => $record['to'],
                        'from' => $record['from'],
                        'fee' => $record['gasUsed'] * $gasPrice,
                        'tx_hash' => $record['transactionHash'],
                        'block_number' => $record['blockNumber'],
                        'payload' => json_encode($record)
                    ]);
                    \Log::info('转账详情 ======> ', [$record]);
                } else {
                    $record = PttCloudAcount::sendTransaction($tx->address, number_format(self::GAS_limit * $gasPrice, 0, '', ''), $gasPrice);
                    \Log::info('gsa转账详情 ======> ', [$record]);
                    TransactionActionHistory::create([
                        'user_id' => $tx->user_id,
                        'symbol' => 'eth',
                        'amount' => self::GAS_limit * $gasPrice,
                        'status' => TransactionActionHistory::STATUS_SUSSESS,
                        'type' => 'gas',
                        'to' => $record['to'],
                        'from' => $record['from'],
                        'fee' => $record['gasUsed'] * $gasPrice,
                        'tx_hash' => $record['transactionHash'],
                        'block_number' => $record['blockNumber'],
                        'payload' => json_encode($record)
                    ]);

                    $this->release(3 * 60);
                }
            } 
        } catch (\Exception $e) {
            \Log::error('队列转账失败 ===> ', [$e->getMessage()]);
            TransactionActionHistory::create([
                'user_id' => $tx->user_id,
                'symbol' => 'ptt',
                'amount' => $tx->amount,
                'status' => TransactionActionHistory::STATUS_FAILED,
                'type' => 'receive',
                'from' => $tx->from,
            ]);
        }
    }
}