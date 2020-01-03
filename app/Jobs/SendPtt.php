<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Services\PttCloudAcount;

class SendPtt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tx;
    
    const transforLimit = 10000;
    
    public function __construct($tx)
	{
	    $this->tx = $tx;
    }
    
    public function handle() 
	{
        $ptt_balance = PttCloudAcount::getBalance($tx->address, 'ptt');

    }
}