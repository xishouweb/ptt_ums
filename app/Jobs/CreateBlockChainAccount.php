<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\User;
use Illuminate\Support\Facades\Hash;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class CreateBlockChainAccount implements ShouldQueue 
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $phone;
	protected $password;
	
	public function __construct($phone, $password = null)
	{
	    $this->phone = $phone;
	    $this->password = $password;
	}

	public function handle() 
	{
		if ($this->attempts() > 3) {
			Log::info("phone is: " . $this->phone);
			return;
		}	

		$url = config('app.node_domain') . "/account";

		$client = new Client();		

		if ($this->password) {
			$res = $client->request('POST', $url, [
				'form_params' => [
					'phone' => $this->phone,
					'password'   => $this->phone . rand(100000, 999999),
				],
			]);
		} else {
			$res = $client->request('POST', $url, [
				'form_params' => [
					'phone' => $this->phone,
					'password'   => $this->password,
				],
			]);
		}
		
		$bodys  = (string) $res->getBody();	

		Log::info($bodys);

		$result = json_decode($bodys);
		if (User::where("phone", $result->phone)->count() <= 0)	
		{
			$user = User::create([
				'phone' => $result->phone,
				'password' => Hash::make('888888'),
			]);		

			if ($user && empty($user->ptt_address)) {
				$user_exist = User::find($user->id);
				$user_exist->ptt_address = $result->address;
				$user_exist->address_password = $result->password;
				$user_exist->save();
			}
		} else {
			$user = User::where("phone", $result->phone)->first();
			if ($user && empty($user->ptt_address)) {
				$user->ptt_address = $result->address;
				$user->address_password = $result->password;
				$user->save();
			}
		}
	}

}
