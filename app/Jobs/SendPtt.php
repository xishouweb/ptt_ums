<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

use App\Services\PttCloudAcount;
use App\Models\UserWallet;
use App\Models\TransactionActionHistory;
use App\Models\UserWalletTransaction;
use App\Models\UserWalletWithdrawal;

class SendPtt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tx;
    protected $withdrawal;

    public $timeout = 180;

    public function __construct($withdrawal, $tx)
	{
	    $this->tx = $tx;
        $this->withdrawal = $withdrawal;
    }

    public function handle()
	{

            $tx = $this->tx;
            $withdrawal = $this->withdrawal;

            \Log::info('队列提币中 ***********> tx_id = ' . $tx->id . '   amount = ' . $withdrawal->amount);

            $gasPrice = PttCloudAcount::getGasPrice();
            $block = PttCloudAcount::sendTransaction($withdrawal->to, bcmul((string)$withdrawal->amount, (string)1000000000000000000),'ptt', [
                'from' => config('app.ptt_master_address'),
                'keystore' => config('app.ptt_master_address_keystore'),
                'password' => config('app.ptt_master_address_password'),
                'ums_tx_id' => $withdrawal->user_wallet_transaction_id,
            ]);
            \Log::info('提币详情tx_id: '. $tx->id .' **********> ', [$block]);
            TransactionActionHistory::create([
                'user_id' => $withdrawal->user_id,
                'symbol' => 'ptt',
                'amount' => $withdrawal->amount,
                'status' => TransactionActionHistory::STATUS_PADDING,
                'type' => 'send',
                'to' => $withdrawal->to,
                'from' => config('app.ptt_master_address'),
                'tx_id' => $withdrawal->user_wallet_transaction_id,
            ]);
    }
}