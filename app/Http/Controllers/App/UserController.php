<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Captcha;
use App\Models\Photo;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
    public function store($phone, $country, $password = '')
    {
        if (!$password) {
            $password = Hash::make($phone);
        } else {
            $password = Hash::make($password);
        }

        $user = User::create([
            'phone'      => $phone,
            'password'   => $password,
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
    public function show(Request $request)
    {
        $user = Auth::user();
        $data['id'] = $user->id;
        $data['phone'] = $user->phone;
        $data['nickname'] = $user->nickname ?: '无代号质子';
        $data['avatar'] = $user->avatar ?: 'http://btkverifiedfiles.oss-cn-hangzhou.aliyuncs.com/photos/2019_11_20_18_27_02_1_4893.png';
        $data['token'] = $request->header('Authorization');
        $data['has_trade_pwd'] = $user->trade_password ? true : false;
        $data['country'] = $user->country ?: '86';
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
    public function update(Request $request)
	{
        if (!$request->input('nickname') && !$request->file('avatar')) {
            return response()->json(['message' => '昵称和头像至少修改一个'], 403);
        }
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
        $data['id'] = $user->id;
        $data['phone'] = $user->phone;
        $data['nickname'] = $user->nickname ?: '无代号质子';
        $data['avatar'] = $user->avatar ?: 'http://btkverifiedfiles.oss-cn-hangzhou.aliyuncs.com/photos/2017_08_21_14_48_05_1_2933.png';
        $data['token'] = $request->header('Authorization');
        return response()->json(['message' => '修改成功', 'data' => $data]);
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
            return response()->json(['message' => '手机号，密码和国家区号均不能为空'], 403);
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
            return response()->json(['message' => '手机号，验证码和国家区号均不能为空'], 403);
        }
        //验证码
        $c_result = Captcha::valid($request->input('phone'), $request->input('captcha'));
        if (!$c_result) {
            return response()->json(['message' => '验证码不存在或过期'], 403);
        }

        //判断用户是否存在
        $content['is_new'] = false;
        $user = User::where('phone', $request->input('phone'))->first();
        if (!$user) {
            $user = $this->store($request->input('phone'), $request->input('country'));
            $content['is_new'] = true;
        }

        $content['token'] = 'Bearer ' . $user->createToken('Wallet')->accessToken;
        $content['message'] = '登录成功';

        return response()->json($content);
    }

    //重置密码
    public function resetPassword(Request $request)
    {
        if (!$request->input('phone') || !$request->input('country') || !$request->input('captcha')) {
            return response()->json(['message' => '请输入正确的参数'], 403);
        }
        if (strlen($request->input('password')) < 6 || strlen($request->input('password')) > 16) {
            return response()->json(['message' => '密码长度需在6-16位'], 403);
        }
        //验证码
        $c_result = Captcha::valid($request->input('phone'), $request->input('captcha'));
        if (!$c_result) {
            return response()->json(['message' => '验证码不存在或过期'], 403);
        }
        $user = User::where('phone', $request->input('phone'))->first();
        $user->password = Hash::make($request->input('password'));
        $user->save();
        return response()->json(['message' => '重置成功']);
    }

    // 验证码注册
    public function signUp(Request $request)
    {
        $phone = $request->input('phone');
        $captcha = $request->input('captcha');
        $country = $request->input('country');
        $password = $request->input('password');
        if (!$phone || !$captcha || !$country || !$password) {
            return $this->error('注册失败');
        }
        // 验证码
        $valid_captcha = Captcha::valid($phone, $captcha);
        if (!$valid_captcha) {
            return $this->error('验证码错误');
        }
        //判断用户是否存在
        try {
            $user = User::where('phone', $phone)->first();
            if (!$user) {
                $user = $this->store($phone, $country, $password);
            }
            $data['token'] = 'Bearer ' . $user->createToken('Wallet')->accessToken;

            $user->findOrCreateEthAccount();
        } catch (\Exception $e) {
            Log::error('注册失败，$phone ' . $phone);
            Log::error($e->getMessage());
            return $this->error('注册失败');
        }
        return $this->apiResponse($data);
    }

    // 验证码登录
    public function signInCaptcha(Request $request)
    {
        $phone = $request->input('phone');
        $captcha = $request->input('captcha');
        if (!$phone || !$captcha) {
            return $this->error('登录失败');
        }
        // 验证码
        $valid_captcha = Captcha::valid($phone, $captcha);
        if (!$valid_captcha) {
            return $this->error('验证码错误');
        }
        $user = User::where('phone', $phone)->first();
        if (!$user) {
            return $this->error('用户不存在');
        }

        $user->findOrCreateEthAccount();

        $data['token'] = 'Bearer ' . $user->createToken('Wallet')->accessToken;
        return $this->apiResponse($data);
    }

    // 密码登录
    public function signInPwd(Request $request)
    {
        $phone = $request->input('phone');
        $password = $request->input('password');
        if (!$phone || !$password) {
            return $this->error('登录失败');
        }

        $result = Auth::attempt(['phone' => $request->input('phone'), 'password' => $request->input('password')]);
        if (!$result) {
            return $this->error('账号或密码错误');
        }
        $user = Auth::user();
        $data['token'] = 'Bearer ' . $user->createToken('Wallet')->accessToken;
        $user->findOrCreateEthAccount();
        
        return $this->apiResponse($data);
    }

    // 重置登录密码
    public function resetSignInPwd(Request $request)
    {
        $phone = $request->input('phone');
        $captcha = $request->input('captcha');
        $password = $request->input('password');
        if (!$phone || !$password || !$captcha) {
            return $this->error();
        }
        if (strlen($request->input('password')) < 8) {
            return $this->error('密码长度需不小于8位');
        }
        // 验证码
        $valid_captcha = Captcha::valid($phone, $captcha);
        if (!$valid_captcha) {
            return $this->error('验证码错误');
        }
        try {
            $user = User::where('phone', $phone)->first();
            if ($user) {
                $user->password = Hash::make($request->input('password'));
                $user->save();
                return $this->success();
            }
        } catch (\Exception $e) {
            Log::error('重置登录密码失败，$phone ' . $phone);
            Log::error($e->getMessage());
        }
        return $this->error();
    }

    // 设置（重置）交易密码
    public function resetTradePwd(Request $request)
    {
        $phone = $request->input('phone');
        $captcha = $request->input('captcha');
        $password = $request->input('password');
        if (!$phone || !$password || !$captcha) {
            return $this->error();
        }
        if (strlen($password) != 6) {
            return $this->error('交易密码需为6位');
        }
        // 验证码
        $valid_captcha = Captcha::valid($phone, $captcha);
        if (!$valid_captcha) {
            return $this->error('验证码错误');
        }
        try {
            $user = User::where('phone', $phone)->first();
            if ($user) {
                $user->trade_password = Hash::make($request->input('password'));
                $user->save();
                return $this->success();
            }
        } catch (\Exception $e) {
            Log::error('设置（重置）交易密码失败，$phone ' . $phone);
            Log::error($e->getMessage());
        }
        return $this->error();
    }
}
