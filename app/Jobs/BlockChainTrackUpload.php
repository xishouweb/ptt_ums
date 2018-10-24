<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\BusinessUser;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class BlockChainTrackUpload implements ShouldQueue 
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	//TODO
    //该队列中address无用，待删

	protected $id;
	protected $content;
	
	public function __construct($id, $content)
	{
	    $this->content = $content;
	    $this->id = $id;
	}

	public function handle() 
	{
		if ($this->attempts() > 3) {
			Log::info("phone is: " . $this->id);
			return;
		}	

		$url = config('app.node_domain') . "/track";
		$client = new Client();		

		$res = $client->request('POST', $url, [
			'form_params' => [
				'content'   => $this->content,
				'dataid'   => $this->id,
			],
		]);
		
		$bodys  = (string) $res->getBody();	
		Log::info($bodys);
		//$result = json_decode($bodys);
		
	}

}
