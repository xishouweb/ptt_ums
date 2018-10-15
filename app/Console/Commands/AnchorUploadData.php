<?php

namespace App\Console\Commands;

use App\Jobs\BlockChainTrackUpload;
use App\Models\TrackItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnchorUploadData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:anchor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将未上链的数据，再次放进队列去调用node';

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
        Log::info('开始');
        DB::table('track_items')->whereNotNull('hx')->orderBy('id')->chunk(100, function ($items){
            foreach ($items as $item) {
                Log::info('anchor数据id : ' . $item->id);
                dispatch((new BlockChainTrackUpload($item->id, json_encode($item->content)))->onQueue('block_chain_data_upload'));
            }
        });
        Log::info('结束');
    }
}
