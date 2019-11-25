<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Dashboard;
use App\Models\TrackItem;
use Illuminate\Http\Request;
use App\Models\DataUid;
use App\Models\UserApplication;
use App\Jobs\CreateBlockChainAccount;
use App\Jobs\BlockChainDataUpload;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Log;
use App\User;


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
		$records = TrackItem::orderBy('id', 'desc')->get();
		return view('record')->with(['records' => $records]);
	}

	public function record(Request $request)
	{
		$dataid = $request->get('dataid');
		$txhash = $request->get('txhash');
		$hashid = $request->get('hashid');

        Log::info('callback $dataid : ' . $dataid);

		if ($data_record = TrackItem::where('id', $dataid)->where('hx', 't')->first()) {
			$data_record->hx = $txhash;
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
		$apikey = $request->get('apikey');
		$user_application_id = $request->get('user_application_id');


		$content = $request->get('content');
		$content_array = json_decode($content);

		if (User::where('phone', $content_array->phone)->count() <= 0) {
			$this->dispatch((new CreateBlockChainAccount($content_array->phone))->onQueue('create_block_chain_account'));
		} 

		$uid_obj = null;
		if (DataUid::where('phone', $content_array->phone)->count() <= 0) {
			$uid_obj = DataUid::create([
				'phone' => $content_array->phone,
			]);		
		} else {
			$uid_obj = DataUid::wherePhone($content_array->phone)->first();	
		}

		$data = [];
		$data['user_application_id'] = $user_application_id;

		if ($vendor = User::whereAddress($address)->where('update_key', $apikey)->first()) {
			$data['user_id'] = $vendor->id;
		} else {
			$data['user_id'] = 0;
		}

		$data['UID'] = $uid_obj->id;
        $data['type'] = TrackItem::TYPE_BUSINESS;
		$data['hx'] = 't';
        $data['content'] = $content;
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

		if ($data_result = TrackItem::create($data)) {
            //相应数据源中的数据数量+1
            $user_application = UserApplication::where('id', $user_application_id)->first();
            if ($user_application) {
                $user_application->count += 1;
                $user_application->save();
            }

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

			$this->dispatch((new BlockChainDataUpload($content_array->source, $content, $data_result->id))->onQueue('block_chain_data_upload'));
		}

		return redirect('/api/vendor/data/record');
	}
}
