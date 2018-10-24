<?php

namespace App\Http\Controllers\Campaign;

use App\Models\ActionHistory;
use App\Models\RentRecord;
use App\Models\TokenVote;
use App\Models\UserToken;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            return $this->apiResponse([], '验证码错误或过期', 1);

        }
        $result = User::where('phone', $phone)->count();

        if ($invite_code = $request->get('invite_code')) {
            $inviter = User::where('invite_code', $invite_code)->first();
            if (!$inviter) {
                return $this->apiResponse([], 'invalid invite code', 1);
            }
        }

        try {
            DB::beginTransaction();

            if (!$result) {
                $user = User::create([
                    'phone' => $phone,
                    'password' => Hash::make($password),
                    'update_key' => md5($phone . env('APP_KEY')),
                    'type' => 'vendor',
                    'invite_code' => User::getInviteCode(),
                ]);

                $inviter->increaseVotes('ptt', User::INVITE_USER_VOTES);
                ActionHistory::record($user->id, User::TYPE_SYSTEM, User::ACTION_REGISTER, null, '用户注册');
                ActionHistory::record($inviter->id, 'system', User::ACTION_INVITE_USER, $user->id, '邀请用户');

            } else {

            }


            DB::commit();
            return $this->apiResponse($user->campaign(1, 'ptt'), '注册成功');

        } catch (\Exception $e) {


        }

    }


    public function detail(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->apiResponse([], '未登录', 1);
        }

        return $this->apiResponse($user->campaign($request->get('campaign_id'), $request->get('token_type')));
    }

    public function teams(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->apiResponse([], '未登录', 1);
        }

        $teams = $this->format_list($user->teams(), ['campaign_id' => $request->get('campaign_id'), 'token_type' => $request->get('token_type')]);

        return $this->apiResponse($teams);
    }

    public function voteTo(Request $request, $team_id)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->apiResponse([], '未登录', 1);
        }

        if (!$team_id) {
            return $this->apiResponse([], '请选择正确的团队', 1);
        }

        if (!$amount = $request->get('amount', 0)) {
            return $this->apiResponse([], '请填写正确的票数', 1);
        }

        if ($team_id === RentRecord::ACTION_SELF_IN . $user->id  && !$user->checkVote()) {
            return $this->apiResponse([], '请先充值', 1);
        }

        if (!$userToken = $user->user_tokens('ptt')) {
            return $this->apiResponse([], '未找到投票信息', 1);
        }

        if ($amount > $userToken->votes) {
            return $this->apiResponse([], '票数不足', 1);
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
            return $this->apiResponse([], $e->getMessage(), 1);
        }

    }

}
