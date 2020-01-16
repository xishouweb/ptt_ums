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
    protected $balance;

    public $timeout = 180;

    public function __construct($withdrawal, $tx, $balance)
	{
	    $this->tx = $tx;
	    $this->withdrawal = $withdrawal;
	    $this->balance = $balance;
    }
    
    public function handle() 
	{
        try {
            $tx = $this->tx;
            $withdrawal = $this->withdrawal;
            $balance = $this->balance;
            \Log::info('队列提币中 ***********> tx_id = ' . $tx->id . '   amount = ' . $withdrawal->amount);

            $gasPrice = PttCloudAcount::getGasPrice();
            $block = PttCloudAcount::sendTransaction($withdrawal->to, bcmul((string)$withdrawal->amount, (string)1000000000000000000), $gasPrice,'ptt', [
                'from' => config('app.ptt_master_address'),
                'keystore' => config('app.ptt_master_address_keystore'),
                'password' => config('app.ptt_master_address_password'),
            ]);
            \Log::info('提币详情 **********> ', [$block]);
            TransactionActionHistory::create([
                'user_id' => $withdrawal->user_id,
                'symbol' => 'ptt',
                'amount' => $withdrawal->amount,
                'status' => TransactionActionHistory::STATUS_SUSSESS,
                'type' => 'send',
                'to' => $block['to'],
                'from' => $block['from'],
                'fee' => $block['gasUsed']  * $gasPrice,
                'tx_hash' => $block['transactionHash'],
                'block_number' => $block['blockNumber'],
                'payload' => json_encode($block),
                'tx_id' => $withdrawal->user_wallet_transaction_id,
            ]);
            
            if(!$block['status']) throw new Exception("转账失败,请检查联系管理员");

            DB::beginTransaction();
            
            $spending = $tx->fee + abs($tx->amount);
            $balance->locked_balance -= $spending;
            $balance->total_balance -= $spending;
            $balance->save();

            $withdrawal->status = UserWalletWithdrawal::COMPLETE_STATUS;
            $withdrawal->from = $block['from'];
            $withdrawal->save();

            $tx->status = UserWalletTransaction::OUT_STATUS_TRANSFER;
            $tx->tx_hash = $block['transactionHash'];
            $tx->from = $block['from'];
            $tx->completed_at = date('Y-m-d H:i:s');
            $tx->save();

            DB::commit();            
        } catch (\Exception $e) {
            DB::rollBack();
            $withdrawal->status = UserWalletWithdrawal::PENDING_STATUS;
            $withdrawal->save();
            \Log::error('队列提币失败 tx_id = '. $tx->id .' ***********> ', [$e->getMessage()]);
            TransactionActionHistory::create([
                'user_id' => $tx->user_id,
                'symbol' => 'ptt',
                'amount' => $tx->amount,
                'status' => TransactionActionHistory::STATUS_FAILED,
                'type' => 'send',
                'from' => $tx->from,
                'tx_id' => $tx->id,
            ]);
        }
    }
}