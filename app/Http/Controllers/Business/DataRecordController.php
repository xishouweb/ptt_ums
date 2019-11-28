<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Jobs\BlockChainDataUpload;
use App\Jobs\CreateBlockChainAccount;
use App\Jobs\HandleUploadFiles;
use App\Models\Dashboard;
use App\Models\DataUid;
use App\Models\TrackItem;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\UserApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;


class DataRecordController extends Controller
{

	public function index()
	{
		$user = Auth::user();
		$items = TrackItem::join('user_applications', 'track_items.user_application_id', '=', 'user_applications.id')
            ->where('track_items.user_id', $user->id)
            ->orderBy('track_items.id', 'desc')
            ->select('track_items.id', 'track_items.hx', 'track_items.created_at', 'track_items.bc_id', 'track_items.type', 'user_applications.name')
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
            if (!$content_array ||
                !array_key_exists('phone',$content_array) ||
                !$content_array['phone'] ||
                strlen((int)$content_array['phone']) != 11
            ) {
                throw new Exception('JSON数据中必须含有正确的手机');
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
            $data['hx'] = 't';
            $data['type'] = TrackItem::TYPE_BUSINESS;
            $data['content'] = $content;
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
                BlockChainDataUpload::dispatch('0x0428e150f72797bdfef7135b11b0953639494f15', $content, $data_result->id)->onQueue('block_chain_data_upload');
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
                    'value' => 1
                ]);
            }

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

    public function decrypt(Request $request)
    {
        $bc_id = $request->input('bcid');
        $pwd = $request->input('pwd');
        Log::info($bc_id . ' : ' .$pwd);
        if (!$bc_id || !$pwd) {
            return $this->error();
        }

        // 根据bc_id获取链上ipfs hash
        $url = 'http://localhost:8888/track/' . $bc_id;
        $bc_result = self::nodeDecrypt($url);
        Log::info($bc_result);
        if ($bc_result) {
            // 解密ipfs hash
            $url = 'http://localhost:8888/decrypt?data=' . $bc_result . '&pwd=' . $pwd;
            $ipfs_result = self::nodeDecrypt($url);
            Log::info($ipfs_result);
            if ($ipfs_result) {
                // 获取ipfs已存储数据
                $url = 'http://ipfs.proton.global/ipfs/' . $ipfs_result->data;
                $data = self::nodeDecrypt($url);
                Log::info($data);
                if ($data) {
                    // 解密ipfs已存储数据
                    $url = 'http://localhost:8888/decrypt?data=' . $data . '&pwd=' . $pwd;
                    $data = self::nodeDecrypt($url);
                    Log::info($data);
                    if ($data) {
                        return $this->apiResponse($data);
                    }
                }
            }
        }

        return $this->error();
    }

    public static function nodeDecrypt($url)
    {
        $client = new Client();
        $res = $client->request('GET', $url);
        $result = json_decode((string)$res->getBody());
        return $result;
    }
}
