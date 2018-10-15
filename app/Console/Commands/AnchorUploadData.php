<?php

namespace App\Console\Commands;

use App\Jobs\BlockChainTrackUpload;
use App\Models\TrackItem;
use GuzzleHttp\Client;
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
        DB::table('track_items')->whereNull('hx')->orderBy('id')->chunk(100, function ($items){
            foreach ($items as $item) {
                Log::info('anchor数据id : ' . $item->id);
                $url = config('app.node_domain') . "/track";
                $client = new Client();

                $res = $client->request('POST', $url, [
                    'form_params' => [
                        'dataid'   => $items->id,
                        'content'   => $items->content,
                    ],
                ]);

                $bodys  = (string) $res->getBody();
                Log::info('node response : ' . $bodys);
                sleep(0.1);
            }
        });
        Log::info('结束');
    }
}
