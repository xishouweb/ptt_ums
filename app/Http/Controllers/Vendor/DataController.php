<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MatchItem;
use GuzzleHttp\Client;
use App\Models\BusinessUser;
use App\Models\DataRecord;
use App\Jobs\CreateBlockChainAccount;
use App\Jobs\BlockChainDataUpload;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Log;


class DataController extends Controller
{
	use DispatchesJobs;

	public function examples_address()
	{
		$address = ['0x0428e150f72797bdfef7135b11b0953639494f15'];	
		//
		$this->dispatch((new CreateBlockChainAccount('18618328615'))->onQueue('create_block_chain_account'));

		return response()->json($address);
	}

	public function index()
	{
		$records = DataRecord::orderBy('id', 'desc')->get();			
		return view('record')->with(['records' => $records]);
	}

	public function record(Request $request)
	{
		Log::info('callback');

		$address = $request->get('address');
		$dataid = $request->get('dataid');
		$txhash = $request->get('txhash');
		$hashid = $request->get('hashid');

		if ($data_record = DataRecord::where('id', $dataid)->first()) {
			$data_record->txhash = $txhash;
			$data_record->bc_id = $hashid;
			$data_record->save();
		}
		
		return response()->json(['msg' => 'success']);
	}

	public function create()
	{
		return view('data_upload');	
	}

	public function store(Request $request)
	{
		$address = $request->get('address');
		$vendor = null;

		if (BusinessUser::whereAddress($address)->count() <= 0)	
		{
			$vendor = BusinessUser::create([
				'address' => $address,
				'type' => 'vendor'
			]);		
		} else {
			$vendor = BusinessUser::whereAddress($address)->first();	
		}

		$content = $request->get('content');
		$content_array = json_decode($content);

		if (BusinessUser::where('phone', $content_array->phone)->count() <= 0) {
			$this->dispatch((new CreateBlockChainAccount($content_array->phone))->onQueue('create_block_chain_account'));
		}

		$data = [];
		$data['b_user_id'] = $vendor->id;
		$data['txhash'] = 't';
		if ($content_array->gender) {
			$data['gender'] = 1;	
		}
		if ($content_array->age) {
			$data['age'] = 1;	
		}
		if ($content_array->user_address) {
			$data['user_address'] = 1;	
		}
		if ($content_array->industry) {
			$data['industry'] = 1;	
		}
		if ($content_array->hobby) {
			$data['hobby'] = 1;	
		}
		if ($content_array->interest) {
			$data['interest'] = 1;	
		}
		if ($content_array->model) {
			$data['model'] = 1;	
		}

		if ($data_result = DataRecord::create($data)) {
			$this->dispatch((new BlockChainDataUpload($content_array->source, $content, $data_result->id))->onQueue('block_chain_data_upload'));
		}

		return redirect('/api/vendor/data/record');
	}
}
