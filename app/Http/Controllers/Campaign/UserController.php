<?php

namespace App\Http\Controllers\Campaign;

use App\Models\ActionHistory;
use App\Models\Captcha;
use App\Models\Photo;
use App\Models\RentRecord;
use App\Models\TokenVote;
use App\Models\UserLogin;
use App\Models\UserToken;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;

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

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error('请先登录~~~!');
        }

        $data['nickname'] = $user->nickname;
        $data['avatar'] = $user->avatar;
        $data['phone'] = $user->phone;
        $data['invite_code'] = $user->invite_code;


        return $this->apiResponse($data);
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
        $user = auth()->user();

        if (!$user) {
            return $this->error('请先登录~~~!');
        }

        $requestData = $request->only(['nickname', 'avatar']);

        if ($user->update($requestData)) {
            return $this->apiResponse();
        }

        return $this->error('更新失败');
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
        $result = Auth::attempt(['phone' => $request->input('phone'), 'password' => $request->input('password')]);
        if ($result) {
            $user = Auth::user();
            $data['token'] = 'Bearer ' . $user->createToken('super_user')->accessToken;
            $data['address'] = $user->address ?: 'Address';
            $data['nickname'] = $user->nickname ?: 'User';
            $data['avatar'] = $user->avatar ?: 'http://btkverifiedfiles.oss-cn-hangzhou.aliyuncs.com/photos/2017_08_21_14_48_05_1_2933.png';
            $data['coins'] = $user->coins;

            try {
                DB::beginTransaction();
                $user->increaseVotes('ptt', 500, 'login');

                UserLogin::record($user, $request->getClientIp(), User::SRC_SUPER_USER, $request->header('user_agent'));
                $user->last_login = date('Y-m-d H:i:s');
                $user->save();

                DB::commit();
                return $this->apiResponse($data, '登录成功', 200);
            } catch (\Exception $e) {
                DB::rollBack();

                \Log::info('用户:' . $user->id . '登陆出错' . $e->getMessage());
            }
        }

        return $this->error('账户不存在或密码错误', 404);

    }

    public function fastLogin(Request $request)
    {
        $phone = $request->input('phone');
        $captcha = $request->input('captcha');

        if (!$phone || !$this->checkPhone($phone)) {
            return $this->error('请确认手机号正确');
        }

        if (!$captcha || !(Captcha::valid($phone, $captcha))) {
            return $this->error('验证码错误或过期');
        }

        $user = User::wherePhone($phone)->first();

        if (!$user) {
            return $this->error('该用户不存在');
        }

        try {
            DB::beginTransaction();

            $user->increaseVotes('ptt', 500, 'login');

            UserLogin::record($user, $request->getClientIp(), User::SRC_SUPER_USER, $request->header('user_agent'));
            $user->last_login = date('Y-m-d H:i:s');
            $user->save();

            $data['token'] = 'Bearer ' . $user->createToken('super_user')->accessToken;
            $data['address'] = $user->address ?: 'Address';
            $data['nickname'] = $user->nickname ?: 'User';
            $data['avatar'] = $user->avatar ?: 'http://btkverifiedfiles.oss-cn-hangzhou.aliyuncs.com/photos/2017_08_21_14_48_05_1_2933.png';
            $data['coins'] = $user->coins;

            DB::commit();
            return $this->apiResponse($data);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->error($e->getMessage());
        }
    }

    public function register(Request $request)
    {
        $phone = $request->input('phone');
        $password = $request->input('password');
        $captcha = $request->input('captcha');

        if (!$password) {
            return $this->error('请填写密码');
        }

        if (!$phone || !$this->checkPhone($phone)) {
            return $this->error('请确认手机号正确');
        }

        $result = User::where('phone', $phone)->count();

        if ($result) {
            return $this->error('该手机号已被注册');
        }

        if (!$captcha || !(Captcha::valid($phone, $captcha))) {
            return $this->error('验证码错误或过期');
        }

        try {
            DB::beginTransaction();

            $user =new User();

            $user->phone = $phone;
            $user->password = Hash::make($password);
            $user->update_key = md5($phone . env('APP_KEY'));
            $user->type = User::SRC_SUPER_USER;
            $user->invite_code = User::getInviteCode();

            $user->save();

            ActionHistory::record($user->id, User::TYPE_SYSTEM, User::ACTION_REGISTER, null, '用户注册');

            if ($invite_code = $request->get('invite_code')) {
                $inviter = User::where('invite_code', $invite_code)->first();
                if (!$inviter) {
                    throw new \Exception('invalid invite code');
                }

                $inviter->increaseVotes('ptt', User::INVITE_USER_VOTES, 'invite_register');
                ActionHistory::record($inviter->id, 'system', User::ACTION_INVITE_USER, $user->id, '邀请用户');
            }

            DB::commit();
            return $this->apiResponse($user->campaign(1, 'ptt'), '注册成功');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('<'.$phone .'>注册失败--超级广告主' . $e->getMessage());
            return $this->error($e->getMessage());
        }

    }

    public function detail(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error('未登录');
        }

        return $this->apiResponse($user->campaign($request->get('campaign_id'), $request->get('token_type')));
    }

    public function teams(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error('未登录');
        }

        $teams = $this->format_list($user->teams(), ['campaign_id' => $request->get('campaign_id'), 'token_type' => $request->get('token_type')]);

        return $this->apiResponse($teams);
    }

    public function voteTo(Request $request, $team_id)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error('未登录');
        }

        if (!$team_id) {
            return $this->error('请选择正确的团队');
        }

        if (!$amount = $request->get('amount', 0)) {
            return $this->error('请填写正确的票数');
        }

        if ($team_id === RentRecord::ACTION_SELF_IN . $user->id  && !$user->checkVote()) {
            return $this->error('请先充值');
        }

        if (!$userToken = $user->user_token('ptt')) {
            return $this->error('未找到投票信息');
        }

        if ($amount > $userToken->votes) {
            return $this->error('票数不足');
        }
        try{
            DB::beginTransaction();
            TokenVote::record($team_id, $user->id, $amount);

            $userToken->votes -= $amount;
            $userToken->save();

            DB::commit();

            return $this->apiResponse();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
        }

    }

    private function checkPhone($phone, $country = '86')
    {
        $data['phone'] = (string)$phone;
        $data['country'] = (string)$country;

        $validator = Validator::make($data, [
            'phone' => 'required|string|size:11',
            'country' => 'required|string',
        ]);

        if($validator->fails()) {
            return false;
        }

        return true;
    }

    public function photoUpload(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error('请先登录~~~!');
        }

        $photo = Photo::upload($request);

        if (!$photo) {
            return $this->error('上传失败!');
        }

        return $this->apiResponse(['url' => $photo->url]);
    }

    public function logout()
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error('请先登录~~~!');
        }

        Auth::logout();

        return $this->apiResponse();
    }
}
