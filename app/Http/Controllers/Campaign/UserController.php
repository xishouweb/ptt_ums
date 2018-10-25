<?php

namespace App\Http\Controllers\Campaign;

use App\Models\ActionHistory;
use App\Models\Captcha;
use App\Models\Photo;
use App\Models\RentRecord;
use App\Models\TokenVote;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
        $result = Auth::attempt(['phone' => $request->input('phone'), 'password' => $request->input('password')]);
        if ($result) {
            $user = Auth::user();
            $data['token'] = 'Bearer ' . $user->createToken('Api')->accessToken;
            $data['address'] = $user->address ?: 'Address';
            $data['nickname'] = $user->nickname ?: 'User';
            $data['avatar'] = $user->avatar ?: 'http://btkverifiedfiles.oss-cn-hangzhou.aliyuncs.com/photos/2017_08_21_14_48_05_1_2933.png';
            $data['coins'] = $user->coins;

            $user->increaseVotes('ptt', 500, 'login');

            return $this->_success_json($data, '登录成功', 200);
        }

        return $this->_bad_json('账户不存在或密码错误', 404);
    }

    public function fastLogin(Request $request)
    {
        $phone = $request->input('phone');
        $captcha = $request->input('captcha');

        if (!$phone || $this->checkPhone($phone)) {
            return $this->_bad_json('请确认手机号正确');
        }

        if (!$captcha || !(Captcha::pre_valid($phone, $captcha))) {
            return $this->_bad_json('验证码错误或过期');
        }

        $user = User::wherePhone($phone)->first();

        if (!$user) {
            return $this->_bad_json('该用户不存在');
        }

        $data['token'] = 'Bearer ' . $user->createToken('Api')->accessToken;
        $data['address'] = $user->address ?: 'Address';
        $data['nickname'] = $user->nickname ?: 'User';
        $data['avatar'] = $user->avatar ?: 'http://btkverifiedfiles.oss-cn-hangzhou.aliyuncs.com/photos/2017_08_21_14_48_05_1_2933.png';
        $data['coins'] = $user->coins;

        return $this->_success_json($data);
    }

    public function register(Request $request)
    {
        $phone = $request->input('phone');
        $password = $request->input('password');
        $captcha = $request->input('captcha');
        $nickname = $request->input('nickname');

        if (!$nickname) {
            return $this->_bad_json('请填写昵称');
        }

        if (!$password) {
            return $this->_bad_json('请填写密码');
        }

        $result = User::where('phone', $phone)->count();

        if ($result) {
            return $this->_bad_json('该手机号已被注册');
        }

        if (!$captcha || !(Captcha::pre_valid($phone, $captcha))) {
            return $this->_bad_json('验证码错误或过期');
        }

        try {
            DB::beginTransaction();


            $avatar = Photo::upload($request, 'avatar');

            $user =new User();

            $user->nickname = $nickname;
            $user->phone = $phone;
            $user->avatar = $avatar->url;
            $user->password = Hash::make($password);
            $user->update_key = md5($phone . env('APP_KEY'));
            $user->type = User::REGISTER_CHANNEL_SUPER_USER;
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
            return $this->_success_json($user->campaign(1, 'ptt'), '注册成功');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('<'.$phone .'>注册失败--超级广告主' . $e->getMessage());
            return $this->_bad_json($e->getMessage());
        }

    }

    public function detail(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->_bad_json('未登录');
        }

        return $this->_success_json($user->campaign($request->get('campaign_id'), $request->get('token_type')));
    }

    public function teams(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->_bad_json('未登录');
        }

        $teams = $this->format_list($user->teams(), ['campaign_id' => $request->get('campaign_id'), 'token_type' => $request->get('token_type')]);

        return $this->_success_json($teams);
    }

    public function voteTo(Request $request, $team_id)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->_bad_json('未登录');
        }

        if (!$team_id) {
            return $this->_bad_json('请选择正确的团队');
        }

        if (!$amount = $request->get('amount', 0)) {
            return $this->_bad_json('请填写正确的票数');
        }

        if ($team_id === RentRecord::ACTION_SELF_IN . $user->id  && !$user->checkVote()) {
            return $this->_bad_json('请先充值');
        }

        if (!$userToken = $user->user_token('ptt')) {
            return $this->_bad_json('未找到投票信息');
        }

        if ($amount > $userToken->votes) {
            return $this->_bad_json('票数不足');
        }
        try{
            DB::beginTransaction();
            TokenVote::record($team_id, $user->id, $amount);

            $userToken->votes -= $amount;
            $userToken->save();

            DB::commit();

            return $this->_success_json();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->_bad_json($e->getMessage());
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
}
