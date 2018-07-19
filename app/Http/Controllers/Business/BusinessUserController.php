<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use App\Models\BusinessUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BusinessUserController extends Controller
{

    protected $content = [];

    public function login(Request $request)
    {
        $result = Auth::attempt(['phone' => $request->input('phone'), 'password' => $request->input('password')]);
        if ($result) {
            $user = Auth::user();
            $this->content['token'] = 'Bearer ' . $user->createToken('Api')->accessToken;
            $this->content['msg'] = '登录成功';
            $this->content['status'] = 200;
            $this->content['address'] = $user->address ?: 'Address';
            $this->content['nickname'] = $user->nickname ?: 'User';
            $this->content['avatar'] = $user->avatar ?: 'http://btkverifiedfiles.oss-cn-hangzhou.aliyuncs.com/photos/2017_08_21_14_48_05_1_2933.png';
            $this->content['coins'] = $user->coins;
        } else {
            $this->content['msg'] = '账户不存在或密码错误';
            $this->content['status'] = 401;
        }
        return response()->json($this->content);
    }

    public function register(Request $request)
    {
        $result = User::where('phone', $request->input('phone'))->count();
        if (!$result) {
            $user = User::create([
                'phone' => $request->input('phone'),
                'password' => Hash::make($request->input('password')),
            ]);
            $this->content['token'] = 'Bearer ' . $user->createToken('Api')->accessToken;
            $this->content['msg'] = '注册成功';
            $status = 200;
        } else {
            $this->content['msg'] = '该手机号已被注册';
            $status = 200;
        }
        return response()->json($this->content, $status);
    }

    public function passport()
    {
        return response()->json(['user' => Auth::user()]);
    }
}
