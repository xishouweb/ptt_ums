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
    
    const TRANSFOR_LIMIT = 10000;
    const GAS_limit = 40000;
    const DECIMALS = 1000000000000000000;

    public function __construct($tx, $type)
	{
	    $this->tx = $tx;
	    $this->type = $type;
    }
    
    public function handle() 
	{
        $tx = $this->tx;
        $symbol = $tx->symbol;
        $user_id = $tx->user_id;
        $amount = $tx->amount;

        if ($this->type = 'receive') {
            $ptt_balance = PttCloudAcount::getBalance($tx->address, 'ptt');

            if ($ptt_balance < self::TRANSFOR_LIMIT * self::DECIMALS) return;

            $eth_balance = PttCloudAcount::getBalance($tx->address);

            if ($eth_balance >= self::GAS_limit) {
                $wallet = UserWallet::whereUserId($tx->user_id)->whereAddress($tx->address)->first();
                $record = PttCloudAcount::sendTransaction(config('app.ptt_master_address'), $tx->amount * self::DECIMALS, 'ptt', [
                    'from' => $tx->address,
                    'keystore' => $wallet->key_store,
                    'password' => decrypt($wallet->password),
                ]);

            } else {
                $user_id = null;
                $record = PttCloudAcount::sendTransaction($tx->address, self::GAS_limit, 'gas');
                $symbol = 'eth';
                $amount = self::GAS_limit / self::DECIMALS;
           
                $this->release(3 * 60);
            }
        } else {
            $ptt_balance = PttCloudAcount::getBalance(config('app.ptt_master_address'), 'ptt');
            if ($ptt_balance < $tx->amount) {
                \Log::error('汇总账户ptt不足, tx_id =' . $tx->id);
                return;
            }
            
            $eth_balance = PttCloudAcount::getBalance(config('app.ptt_master_address'));
            if ($eth_balance < self::GAS_limit) {
                \Log::error('汇总账户gas不足, 提币失败, tx_id =' . $tx->id);
                return;
            }

            $record = PttCloudAcount::sendTransaction($tx->to, $tx->amount * self::DECIMALS, 'ptt', [
                'from' => config('app.ptt_master_address'),
                'keystore' => config('app.ptt_master_address_keystore'),
                'password' => config('app.ptt_master_address_password'),
            ]);

       
        }

        TransactionActionHistory::create([
            'user_id' => $user_id,
            'symbol' => $symbol,
            'amount' => $amount,
            'to' => $record['to'],
            'from' => $record['from'],
            'fee' => $record['gasUsed'] / self::DECIMALS,
            'tx_hash' => $record['transactionHash'],
            'block_number' => $record['blockNumber'],
            'payload' => json_encode($record)
        ]);
        


    }
}