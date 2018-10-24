<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Captcha;
use App\Models\Photo;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $phone = $request->input('phone');
        $country = $request->input('country');

        $user = User::create([
            'phone'      => $phone,
            'password'   => Hash::make($phone),
            'update_key' => md5($phone . env('APP_KEY')),
            'type'       => 'wallet',
            'country'    => $country,
        ]);

        return $user;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $user = Auth::user();
        $data['id'] = $user->id;
        $data['phone'] = $user->phone;
        $data['nickname'] = $user->nickname ?: 'User_' . md5($user->phone);
        $data['avatar'] = $user->avatar ?: 'http://btkverifiedfiles.oss-cn-hangzhou.aliyuncs.com/photos/2017_08_21_14_48_05_1_2933.png';
        return response()->json($data);
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
        $user = Auth::user();
        if ($request->input('nickname')) {
            $user->nickname = $request->input('nickname');
        }
        if ($request->file('avatar')) {
            $photo = Photo::upload($request, 'avatar');
            if (!$photo) {
                return response()->json(['message' => '图片上传失败'], 500);
            }
            $user->avatar = $photo->url;
        }
        $user->save();
        return response()->json(['message' => '修改成功']);
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

    //密码登录
    public function login(Request $request)
    {
        if (!$request->input('phone') || !$request->input('country') || !$request->input('password')) {
            return response()->json(['message' => '手机号，密码和国家区号均不能为空'], 401);
        }

        $result = Auth::attempt(['phone' => $request->input('phone'), 'password' => $request->input('password')]);
        if (!$result) {
            return response()->json(['message' => '账户不存在或密码错误'], 403);
        }

        $user = Auth::user();
        $content['token'] = 'Bearer ' . $user->createToken('Wallet')->accessToken;
        $content['message'] = '登录成功';
        return response()->json($content);
    }

    //验证码登录
    public function fastLogin(Request $request)
    {
        if (!$request->input('phone') || !$request->input('country') || !$request->input('captcha')) {
            return response()->json(['message' => '手机号，验证码和国家区号均不能为空'], 401);
        }
        //验证码
        $c_result = Captcha::valid($request->input('phone'), $request->input('captcha'));
        if (!$c_result) {
            return response()->json(['message' => '验证码不存在或过期'], 401);
        }

        //判断用户是否存在
        $content['is_new'] = false;
        $user = User::where('phone', $request->input('phone'))->first();
        if (!$user) {
            $user = $this->store($request);
            $content['is_new'] = true;
        }

        $content['token'] = 'Bearer ' . $user->createToken('Wallet')->accessToken;
        $content['message'] = '登录成功';

        return response()->json($content);
    }
}
