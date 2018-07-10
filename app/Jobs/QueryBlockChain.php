<?php

namespace App\Jobs;

use App\Models\MatchItem;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class QueryBlockChain implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $match_item;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MatchItem $match_item)
    {
        $this->match_item = $match_item;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('handle');
        Log::info($this->match_item);
    }
}
