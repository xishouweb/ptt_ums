<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Jobs\BlockChainDataUpload;
use App\Jobs\CreateBlockChainAccount;
use App\Jobs\HandleUploadFiles;
use App\Models\DataRecord;
use App\Models\DataUid;
use App\User;
use Illuminate\Http\Request;
use App\Models\UserApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;

class DataRecordController extends Controller
{

	public function index(Request $request)
	{
		$user = Auth::user();
		$items = DataRecord::join('user_applications', 'data_records.user_application_id', '=', 'user_applications.id')
            ->where('data_records.user_id', $user->id)
            ->orderBy('data_records.id', 'desc')
            ->select('data_records.id', 'data_records.txhash', 'data_records.created_at', 'data_records.bc_id', 'user_applications.name')
            ->paginate(10);
		return response()->json(['data' => $items]);
	}

    public function store(Request $request)
    {
        $vendor = Auth::user();

        $api_key = $request->input('api_key');
        $public_key = $request->input('public_key');
        $user_application_id = $request->input('user_application');
        $content = $request->input('content');

        $valid = [
            'api_key' => $api_key,
            'public_key' => $public_key,
            'user_application_id' => $user_application_id,
            'content' => $content,
        ];
        $validator = Validator::make($valid, [
            'api_key' => 'required',
            'public_key' => 'required',
            'user_application_id' => 'required',
            'content' => 'required',
        ]);
        if ($validator->fails()) {
            $response_data['status'] = 401;
            $response_data['msg'] = '当前页面均为必填';
            return response()->json($response_data);
        }

        if ($vendor->update_key != $api_key || $vendor->ptt_address != $public_key) {
            $response_data['status'] = 401;
            $response_data['msg'] = 'Api Key或Public Key不正确';
            return response()->json($response_data);
        }

        $content_array = json_decode($content, true);
        if (!$content_array || !$content_array['phone']) {
            $response_data['status'] = 401;
            $response_data['msg'] = 'JSON数据中必须含有手机';
            return response()->json($response_data);
        }

        $res = self::processingData($vendor, $user_application_id, $content, $content_array);

        return response()->json($res);
    }

    public static function  processingData ($vendor, $user_application_id, $content, $content_array) {
	    try {
            if (!$content_array || !array_key_exists('phone',$content_array) || !$content_array['phone']) {
                throw new Exception('JSON数据中必须含有手机');
            }

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

            if ($data_result = DataRecord::create($data)) {
                BlockChainDataUpload::dispatch('0x0428e150f72797bdfef7135b11b0953639494f15', $content, $data_result->id)->onQueue('block_chain_data_upload');
            }

            $user_application = UserApplication::where('id', $user_application_id)->first();
            $user_application->count += 1;
            $user_application->save();

            $response_data['status'] = 200;
            $response_data['msg'] = '上传成功';
        } catch (Exception $exception) {
	        Log::error($exception->getMessage());
            $response_data['status'] = 401;
            $response_data['msg'] = '上传失败，请重试';
        }

        return $response_data;
    }

    public function uploadFile(Request $request)
    {
        $file = $request->file('file');
        $valid = [
            'file' => $file,
        ];
        $validator = Validator::make($valid, [
            'file' => 'required',
        ]);
        if ($validator->fails()) {
            $data['status'] = 401;
            $data['msg'] = '请上传文件';
            return response()->json($data);
        }

        $path = $file->store('upload');

        $data['status'] = 200;
        $data['msg'] = '上传成功';
        $data['code'] = 0;
        $data['imgURL'] = $path;
        return response()->json($data);
    }

    public function multipleDataUpload(Request $request)
    {
        $vendor = Auth::user();

        $api_key = $request->input('api_key');
        $public_key = $request->input('public_key');
        $user_application_id = $request->input('user_application');
        $file = $request->input('file');

        $valid = [
            'api_key' => $api_key,
            'public_key' => $public_key,
            'user_application_id' => $user_application_id,
            'file' => $file,
        ];
        $validator = Validator::make($valid, [
            'api_key' => 'required',
            'public_key' => 'required',
            'user_application_id' => 'required',
            'file' => 'required',
        ]);
        if ($validator->fails()) {
            $data['status'] = 401;
            $data['msg'] = '当前页面均为必填';
            return response()->json($data);
        }

        if ($vendor->update_key != $api_key || $vendor->ptt_address != $public_key) {
            $response_data['status'] = 401;
            $response_data['msg'] = 'Api Key或Public Key不正确';
            return response()->json($response_data);
        }

        foreach ($file['fileList'] as $item) {
            HandleUploadFiles::dispatch($vendor, $item['imgURL'], $user_application_id);
        }

        $data['status'] = 200;
        $data['msg'] = '上传成功';
        return response()->json($data);
    }
}
