<?php

namespace App\Http\Controllers\SDK;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrackItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Jobs\CreateBlockChainAccount;
use App\Models\Dashboard;
use App\Models\DataUid;
use App\Models\UserApplication;
use App\User;

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
		$content = $params['content'] = $request->get('content');
		$sign = $request->get('sign');
		$api_key = $params['api_key'] = $request->get('api_key');
		$user_application_id = $params['user_application'] = $request->input('user_application');

		$vendor = User::where('ptt_address', $api_key)->first();

		if(!$vendor){
			return response()->json(['code' => -11001, 'msg'=>'invalid credentials']);
		};

		//相应数据源中的数据数量+1
		$user_application = UserApplication::where('id', $user_application_id)->first();

		if(!$user_application) {
			return response()->json(['code' => -11004, 'msg'=>'Do not find the data source, please check it!']);
		}

		if(!$this->__checkSign($sign, $vendor, $params)){
			return response()->json(['code' => -11001, 'msg'=>'invalid sign']);
		}

		$content_array = json_decode($content, true);

		if (User::where('phone', $content_array['phone'])->count() <= 0) {
			CreateBlockChainAccount::dispatch($content_array['phone'])->onQueue('create_block_chain_account');
		}

		$uid_obj = null;
		if (DataUid::where('phone', $content_array['phone'])->count() <= 0) {
			$uid_obj = DataUid::create([
				'phone' => $content_array['phone'],
			]);
		} else {
			$uid_obj = DataUid::wherePhone($content_array['phone'])->first();
		}

		$data = [];
		$data['user_application_id'] = $user_application_id;
		$data['user_id'] = $vendor->id;
		$data['UID'] = $uid_obj->id;
		$data['txhash'] = 't';
		$data['type'] = TrackItem::TYPE_BUSINESS;
		if (array_key_exists('gender',$content_array) && $content_array['gender']) {
			$data['gender'] = 1;
		}
		if (array_key_exists('age',$content_array) && $content_array['age']) {
			$data['age'] = 1;
		}
		if (array_key_exists('user_address',$content_array) && $content_array['user_address']) {
			$data['user_address'] = 1;
		}
		if (array_key_exists('industry',$content_array) && $content_array['industry']) {
			$data['industry'] = 1;
		}
		if (array_key_exists('hobby',$content_array) && $content_array['hobby']) {
			$data['hobby'] = 1;
		}
		if (array_key_exists('interest',$content_array) && $content_array['interest']) {
			$data['interest'] = 1;
		}
		if (array_key_exists('model',$content_array) && $content_array['model']) {
			$data['model'] = 1;
		}


		if ($data_result = TrackItem::create($data)) {

            $redis = Redis::connection('default');
            try {
                Log::info('存储redis , anchor数据id : ' . $data_result->id);
                $redis->lpush('anchor:test:channel', json_encode([
                    'data_id' => $data_result->id,
                    'content' => $content
                ]));
            } catch (\Exception $exception) {
                Log::info($exception->getMessage());
				Log::info('存储redis失败 , anchor数据id : ' . $data_result->id);
				return response()->json(['code' => -11003, 'msg'=>'存储失败 请联系管理员']);
			}
			
			//相应数据源中的数据数量+1
			$user_application = UserApplication::where('id', $user_application_id)->first();
			$user_application->count += 1;
			$user_application->save();

			//记录当天上传数据量
			$upload_record = Dashboard::where('user_id', $vendor->id)
				->where('created_at', '>=', date('Y-m-d 00:00:00'))
				->where('created_at', '<=', date('Y-m-d 23:59:59'))
				->first();
			if ($upload_record) {
				$upload_record->value += 1;
				$upload_record->save();
			} else {
				Dashboard::create([
					'user_id' => $vendor->id,
					'type' => Dashboard::UPLOAD_DATA,
					'value' => 1,
				]);
			}


            return response()->json(['code' => 200, 'msg'=>'success']);
		}

	
		
        return response()->json(['code' => -11002, 'msg'=>'failed']);	
	}

	private function __checkSign($sign, $vendor, $params)
	{
		\ksort($params);
		
		$checkSign = md5(http_build_query($params) . $vendor->update_key);

		if($sign != $checkSign) return 0;

		return 1;
	}
	
}
