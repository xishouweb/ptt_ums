<?php

namespace App\Console\Commands;

use App\Jobs\BlockChainTrackUpload;
use App\Models\TrackItem;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ItemJson;

class AnalysisItem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analysis:item';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'analysis';

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
		$items = TrackItem::where('id', '>', 59)->orderBy('id', 'asc')->chunk(100, function($items) {
			foreach ($items as $item) {
				$item_obj = json_decode($item->content);

				$data = ['item_id' => $item->id];
				if (isset($item_obj->k)) {
					$data['k'] = $item_obj->k;
				}
				if (isset($item_obj->p)) {
					$data['p'] = $item_obj->p;
				}
				if (isset($item_obj->dx)) {
					$data['dx'] = $item_obj->dx;
				}
				if (isset($item_obj->rt)) {
					$data['rt'] = $item_obj->rt;
				}
				if (isset($item_obj->ns)) {
					$data['ns'] = $item_obj->ns;
				}
				if (isset($item_obj->ni)) {
					$data['ni'] = $item_obj->ni;
				}
				if (isset($item_obj->v)) {
					$data['v'] = $item_obj->v;
				}
				if (isset($item_obj->xa)) {
					$data['xa'] = $item_obj->xa;
				}
				if (isset($item_obj->tr)) {
					$data['tr'] = $item_obj->tr;
				}
				if (isset($item_obj->mo)) {
					$data['mo'] = $item_obj->mo;
				}
				if (isset($item_obj->m0)) {
					$data['m0'] = $item_obj->m0;
				}
				if (isset($item_obj->m0a)) {
					$data['m0a'] = $item_obj->m0a;
				}
				if (isset($item_obj->m1)) {
					$data['m1'] = $item_obj->m1;
				}
				if (isset($item_obj->m1a)) {
					$data['m1a'] = $item_obj->m1a;
				}

				if (isset($item_obj->m2)) {
					$data['m2'] = $item_obj->m2;
				}
				if (isset($item_obj->m4)) {
					$data['m4'] = $item_obj->m4;
				}
				if (isset($item_obj->m5)) {
					$data['m5'] = $item_obj->m5;
				}
				if (isset($item_obj->m6)) {
					$data['m6'] = $item_obj->m6;
				}
				if (isset($item_obj->m6a)) {
					$data['m6a'] = $item_obj->m6a;
				}
				if (isset($item_obj->vo)) {
					$data['vo'] = $item_obj->vo;
				}
				if (isset($item_obj->vr)) {
					$data['vr'] = $item_obj->vr;
				}
				if (isset($item_obj->o)) {
					$data['o'] = $item_obj->o;
				}

				ItemJson::create($data);	
			}	
		});
    }
}
