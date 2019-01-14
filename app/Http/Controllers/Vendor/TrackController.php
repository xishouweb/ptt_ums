<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\TrackItem;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\BlockChainTrackUpload;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TrackController extends Controller
{

	public function index()
	{
		$records = TrackItem::whereNotNull('hx')->orderBy('id', 'desc')->paginate(100);			
		return view('track')->with(['records' => $records]);
	}

	public function record(Request $request)
	{
		$dataid = $request->get('dataid');
		$txhash = $request->get('txhash');
		if ($data_record = TrackItem::where('id', $dataid)->first()) {
            Log::info('track callback dataid:' . $dataid . ' txhash : ' . $txhash . ' hx : ' . $data_record->hx);
			$data_record->hx = $txhash;
			$data_record->save();
		}
		
		return response()->json(['msg' => 'success']);
	}

	public function upload(Request $request)
	{
		$data = $request->all();

		if ($item = TrackItem::create(['content' => json_encode($data)])) {
//			$this->dispatch((new BlockChainTrackUpload($item->id, json_encode($data)))->onQueue('block_chain_data_upload'));
            $redis = Redis::connection('default');
            try {
                Log::info('存储redis , anchor数据id : ' . $item->id);
                $redis->lpush('anchor:test:channel', json_encode([
                    'data_id' => $item->id,
                    'content' => $item->content
                ]));
            } catch (\Exception $exception) {
                Log::info($exception->getMessage());
                Log::info('存储redis失败 , anchor数据id : ' . $item->id);
            }
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

		if (isset($data['o']) && !empty($data['o']) && $data['o'] == 'false')
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
