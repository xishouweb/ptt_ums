<?php

namespace App\Jobs;

use App\Models\MarkSixBetHistory;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarkSixCheckTransactionStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    protected $id;
    protected $tx_hash;

    const TEST_API = 'https://api-ropsten.etherscan.io/api?module=transaction&action=gettxreceiptstatus&apikey=YourApiKeyToken&txhash=';
    const LIVE_API = 'https://api.etherscan.io/api?module=transaction&action=gettxreceiptstatus&apikey=YourApiKeyToken&txhash=';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($history_id, $tx_hash)
    {
        $this->id = $history_id;
        $this->tx_hash = $tx_hash;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $url = self::TEST_API . $this->tx_hash;
        try {
            $client = new Client();
            $response = $client->request('get', $url);
        } catch (ClientException $exception) {
            Log::info($exception->getMessage());
            Log::error('发送请求失败，history数据id : ' . $this->id . ' host : ' . $url);
        }
        $model = json_decode($response->getBody());
        $history = MarkSixBetHistory::find($this->id);
        if ($model->result->status === '1') {
            $history->status = MarkSixBetHistory::STATUS_SUCCESS_BETTING;
        } else if ($model->result->status === '0') {
            $history->status = MarkSixBetHistory::STATUS_FAILURE_BETTING;
        } else {
            if (strtotime($history->created_at) > strtotime(now()->subDays(2))) {
                dispatch(new MarkSixCheckTransactionStatus($this->id, $this->tx_hash))->delay(now()->addMinutes(30))->onQueue('check');
            } else {
                $history->status = MarkSixBetHistory::STATUS_FAILURE_BETTING;
            }
        }
        $history->save();
    }
}
