<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\BusinessUser;

use GuzzleHttp\Client;

class CreateBlockChainAccount implements ShouldQueue 
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $phone;
	
	public function __construct($phone)
	{
	    $this->phone = $phone;
	}

	public function handle() 
	{
		if ($this->attempts() > 3) {
			Log::info("phone is: " . $this->phone);
			return;
		}	

		$url = "http://p1.analytab.net:8888/account";

		$client = new Client();		

		$res = $client->request('POST', $url, [
			'form_params' => [
				'phone' => $this->phone,
				'password'   => $this->phone . rand(100000, 999999),
			],
		]);
		
		$bodys  = (string) $res->getBody();	

		\Log::info($bodys);

		$result = json_decode($bodys);
		if (BusinessUser::where("phone", $result->phone)->count() <= 0)	
		{
			BusinessUser::create([
				'address' => $result->address,
				'phone' => $result->phone,
				'address_password' => $result->password,
			]);		
		}
	}

}
