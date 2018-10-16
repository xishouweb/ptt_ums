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
            $urls = [
                config('app.node_domain') . "/track",
                config('app.node_hk') . "/track",
            ];
            $i = 0;
            foreach ($items as $item) {
                try {
                    if ($i < count($urls) - 1) {
                        $i++;
                    } else {
                        $i = 0;
                    }
                    Log::info('anchor数据id : ' . $item->id . ' , host : ' . $urls[$i]);
                    $client = new Client();
                    $client->request('POST', $urls[$i], [
                        'form_params' => [
                            'dataid'   => $item->id,
                            'content'   => $item->content,
                        ],
                    ]);
                } catch (\Exception $exception) {
                    Log::info($exception->getMessage());
                    Log::info('发送请求失败 , anchor数据id : ' . $item->id . ' host : ' . $urls[$i]);
                }
            }
        });
        Log::info('结束');
    }
}
