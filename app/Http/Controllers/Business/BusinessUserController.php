<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Jobs\CreateBlockChainAccount;
use App\Models\Captcha;
use App\User;
use Illuminate\Http\Request;
use App\Models\BusinessUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\DispatchesJobs;

class BusinessUserController extends Controller
{

	use DispatchesJobs;

    protected $content = [];
	
	// 产品公钥
	public function generate_public_key(Request $request)
	{
		$user = Auth::user();
		$password = $request->get('password');
		if ($password) {
            $this->dispatch((new CreateBlockChainAccount($user->phone, $password))->onQueue('create_block_chain_account'));
            $data['status'] = 200;
            $data['msg'] = '生成成功';
        } else {
            $data['status'] = 401;
            $data['msg'] = '生成失败';
        }
		return response()->json($data);
	}

    public function login(Request $request)
    {
        $result = Auth::attempt(['phone' => $request->input('phone'), 'password' => $request->input('password')]);
        if ($result) {
            $user = Auth::user();
            $this->content['token'] = 'Bearer ' . $user->createToken('Api')->accessToken;
            $this->content['address'] = $user->address ?: 'Address';
            $this->content['nickname'] = $user->nickname ?: 'User';
            $this->content['avatar'] = $user->avatar ?: 'http://btkverifiedfiles.oss-cn-hangzhou.aliyuncs.com/photos/2017_08_21_14_48_05_1_2933.png';
            $this->content['coins'] = $user->coins;
            $this->content['msg'] = '登录成功';
            $this->content['status'] = 200;
        } else {
            $this->content['msg'] = '账户不存在或密码错误';
            $this->content['status'] = 401;
        }
        return response()->json($this->content);
    }

    public function register(Request $request)
    {
        $phone = $request->input('phone');
        $password = $request->input('password');
        $captcha = $request->input('captcha');
        $c_result = Captcha::pre_valid($phone, $captcha);
        if (!$c_result) {
            $this->content['msg'] = '验证码错误或过期';
            $this->content['status'] = 401;
            return response()->json($this->content);
        }
        $result = User::where('phone', $phone)->count();
        if (!$result) {
            User::create([
                'phone' => $phone,
                'password' => Hash::make($password),
                'update_key' => md5($phone . env('APP_KEY')),
                'type' => 'vendor',
            ]);
            $this->content['msg'] = '注册成功';
            $this->content['status'] = 200;
        } else {
            $this->content['msg'] = '该手机号已被注册';
            $this->content['status'] = 401;
        }
        return response()->json($this->content);
    }

    public function detail()
    {
        $user = Auth::user();
        $user->address = $user->address ?: '';
        $user->nickname = $user->nickname ?: 'User';
        $user->email = $user->email ?: '';
        $user->avatar = $user->avatar ?: 'http://btkverifiedfiles.oss-cn-hangzhou.aliyuncs.com/photos/2017_08_21_14_48_05_1_2933.png';
        $user->ptt_address = $user->ptt_address ?: '';
        return response()->json(Auth::user());
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $data = $request->only(['nickname', 'email', 'avatar', 'phone', 'eth_address', 'api_key']);
        $user->nickname = $user->nickname == $data['nickname'] ? $user->nickname : $data['nickname'];
        $user->email = $user->email == $data['email'] ? $user->email : $data['email'];
        $user->address = $user->address == $data['eth_address'] ? $user->address : $data['eth_address'];
        $user->save();
        return response()->json(['status' => 200, 'msg' => '更新成功']);
    }
}
