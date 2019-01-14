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

class BlockChainDataUpload implements ShouldQueue 
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	//TODO
    //该队列中address无用，待删

	protected $id;
	protected $content;
	protected $address;
	
	public function __construct($address, $content, $id)
	{
	    $this->content = $content;
	    $this->id = $id;
	    $this->address = $address;
	}

	public function handle() 
	{
	    Log::info('handle');
		if ($this->attempts() > 3) {
			Log::info("phone is: " . $this->id);
			return;
		}	

		$url = config('app.node_domain') . "/upload";
		$client = new Client();		

		$res = $client->request('POST', $url, [
			'form_params' => [
				'address'   => $this->address,
				'hash'   => $this->content,
				'dataid'   => $this->id,
			],
		]);
		
		$bodys  = (string) $res->getBody();	
		Log::info($bodys);
		//$result = json_decode($bodys);
		
	}

}
