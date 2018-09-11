<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Captcha;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function login(Request $request)
    {
        $phone = $request->input('phone');
        $country = $request->input('country');
        $captcha = $request->input('captcha');

        if (!$phone || !$country) {
            return response()->json(['error' => '手机号和国家区号不能为空'], 401);
        }
        //验证码
//        $c_result = Captcha::pre_valid($phone, $captcha);
//        if (!$c_result) {
//            return response()->json(['error' => '验证码错误或过期'], 401);
//        }

        //判断用户是否存在
        $user = User::where('phone', $phone)->first();
        if (!$user) {
            $user = User::create([
                'phone'      => $phone,
                'password'   => Hash::make($phone),
                'update_key' => md5($phone . env('APP_KEY')),
                'type'       => 'wallet',
                'country'    => $country,
            ]);
        }

        $content['token'] = 'Bearer ' . $user->createToken('Wallet')->accessToken;
        $content['data'] = '登录成功';

        return response()->json($content);
    }

    public function detail()
    {
        $user = Auth::user();
        $data['phone'] = $user->phone;
        $data['nickname'] = $user->nickname ?: 'User_' . md5($user->phone);
        $data['avatar'] = $user->avatar ?: 'http://btkverifiedfiles.oss-cn-hangzhou.aliyuncs.com/photos/2017_08_21_14_48_05_1_2933.png';
        return response()->json($data);
    }
}
