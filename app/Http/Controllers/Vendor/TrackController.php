<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\TrackItem;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Log;
use App\Jobs\BlockChainTrackUpload;

class TrackController extends Controller
{

	public function index()
	{
		$records = TrackItem::orderBy('id', 'desc')->get();			
		return view('track')->with(['records' => $records]);
	}

	public function record(Request $request)
	{
		Log::info('track callback');
		
		$dataid = $request->get('dataid');
		$txhash = $request->get('txhash');
		
		if ($data_record = TrackItem::where('id', $dataid)->first()) {
			$data_record->hx	= $txhash;
			$data_record->save();
		}
		
		return response()->json(['msg' => 'success']);
	}

	public function upload(Request $request)
	{
		$data = $request->all();

		if ($item = TrackItem::create(['content' => json_encode($data)])) {
			$this->dispatch((new BlockChainTrackUpload($item->id, json_encode($data)))->onQueue('block_chain_data_upload'));
		}

		if (isset($data['rd'])) {
			$params = $_SERVER['QUERY_STRING'];
			$url = substr($params, strpos($params, 'http'), strlen($params));
			Header("Location: " . $url);
			exit;
		}

		if (!isset($data['o']))
		{
			header('Content-Type: image/gif');
			die(hex2bin('47494638396101000100900000ff000000000021f90405100000002c00000000010001000002020401003b'));
		}

		if (!isset($data['o']) && empty($data['o']))
		{
			header('Content-Type: image/gif');
			die(hex2bin('47494638396101000100900000ff000000000021f90405100000002c00000000010001000002020401003b'));
		}


		if (isset($data['o']) && !empty($data['o']))
		{
			Header("Location: " . $data['o']);
			exit;
		}

		//return response()->json(['code' => 200, 'msg'=>'success']);
	}

}
