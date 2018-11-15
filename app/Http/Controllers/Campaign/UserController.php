<?php

namespace App\Http\Controllers\Campaign;

use App\Models\ActionHistory;
use App\Models\Captcha;
use App\Models\DataCache;
use App\Models\Photo;
use App\Models\RentRecord;
use App\Models\Team;
use App\Models\TokenVote;
use App\Models\UserAddress;
use App\Models\UserLogin;
use App\Models\UserToken;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Mockery\Exception;

class UserController extends Controller
{
    use DispatchesJobs;

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

        $requestData = $request->only(['nickname', 'avatar']);

        if ($user->update($requestData)) {
            return $this->apiResponse(['nickname' => $user->nickname, 'avatar' => $user->avatar]);
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

            $data = $user->baseInfo();

            try {
                DB::beginTransaction();
                $user->increaseVotes('ptt', User::LOGIN_VOTES, 'login');

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

        return $this->error('账户不存在或密码错误', 401);

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

            $user->increaseVotes('ptt', User::LOGIN_VOTES, 'fast_login');

            UserLogin::record($user, $request->getClientIp(), User::SRC_SUPER_USER, $request->header('user_agent'));

            $user->last_login = date('Y-m-d H:i:s');
            $user->save();

            $data = $user->baseInfo();

            DB::commit();
            return $this->apiResponse($data);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            return $this->error();
        }
    }

    public function register(Request $request)
    {
        $phone = $request->input('phone');
        $captcha = $request->input('captcha');


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
            $user->update_key = md5($phone . env('APP_KEY'));
            $user->type = User::SRC_SUPER_USER;
            $user->password = Hash::make($phone);
            $user->invite_code = User::getInviteCode();

            $user->save();

            ActionHistory::record($user->id, User::ACTION_REGISTER, null, null,'用户注册');
            UserLogin::record($user, $request->getClientIp(), User::SRC_SUPER_USER, $request->header('user_agent'));
            $user->increaseVotes('ptt', User::LOGIN_VOTES, 'login');

            if ($invite_code = $request->get('inviteCode')) {
                $inviter = User::where('invite_code', $invite_code)->first();
                if (!$inviter) {
                    throw new \Exception('invalid invite code');
                }

                $inviter->increaseVotes('ptt', User::INVITE_USER_VOTES, 'invite_register');
                ActionHistory::record($inviter->id,User::ACTION_INVITE_USER, null, User::INVITE_USER_VOTES,'邀请好友', ActionHistory::TYPE_VOTE, $user->id);
            }

            DB::commit();

            $data = $user->baseInfo();

            return $this->apiResponse($data, '注册成功');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('<'.$phone .'>注册失败--超级广告主' . $e->getMessage());
            return $this->error();
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

        if (!$team_id) {
            return $this->error('请选择正确的团队');
        }

        if (!$amount = $request->get('amount', 0)) {
            return $this->error('请填写正确的票数');
        }

        if (!$userToken = $user->user_token('ptt')) {
            return $this->error('请先充值');
        }

        if ($amount > ($userToken->votes + $userToken->temp_votes)) {
            return $this->error('票数不足');
        }
        try{
            DB::beginTransaction();
            TokenVote::record($team_id, $user->id, $amount);

            if (substr($team_id, 0, 8) == RentRecord::ACTION_SELF_IN) {
                $person_team = User::find((int)substr($team_id, 8));
                if (!$person_team) {
                    throw new \Exception('请选择正确的战队');
                }

                $name = $person_team->nickname;
            } else {
                $team = Team::find($team_id);
                if (!$team) {
                    throw new \Exception('请选择正确的战队');
                }

                $name = $team->team_name;
            }

            ActionHistory::record($user->id, User::ACTION_VOTE, $team_id, -$amount,'投票' . $name, ActionHistory::TYPE_VOTE);
            if ($amount > $userToken->temp_votes) {

                $userToken->votes -= ($amount - $userToken->temp_votes);
                $userToken->temp_votes = 0;
            } else {
                $userToken->temp_votes -= $amount;
            }
            $userToken->save();

            DataCache::zincrOfCreditRankFor($team_id, $amount * User::CREDIT_VOTE_RATIO);

            DB::commit();

            return $this->apiResponse();
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            return $this->error();
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
        if (Auth::check()) {
            Auth::user()->token()->revoke();
            return $this->success();
        }else {
            return $this->error('something went wrong');
        }
    }

    public function checkNickname($nickname)
    {
        $user = User::where('nickname', $nickname)->first();

        if ($user) {
            return $this->error('该昵称被占用~');
        }

        return $this->success();
    }

    public function updatePassword(Request $request)
    {
        $password = $request->input('password');

        $phone = $request->input('phone');
        $captcha = $request->input('captcha');

        if (!$password) {
            return $this->error('参数错误');
        }

        if (!$phone || !$this->checkPhone($phone)) {
            return $this->error('请确认手机号正确');
        }

        if (!$captcha || !(Captcha::valid($phone, $captcha))) {
            return $this->error('验证码错误或过期');
        }

        $user = User::wherePhone($phone)->first();

        if (!$user) {
            return $this->error('未找到该用户相关信息, 请核对手机号码');
        }

        $res = $user->update(['password' => $user->createPassword($password)]);

        if (!$res) {
            return $this->error();
        }

        return $this->success();
    }

    public function getVotes()
    {
        $user = auth()->user();

        return $this->apiResponse([$user->votes + $user->temp_votes]);
    }

    public function getVoteDetail()
    {
        $user = auth()->user();

        $data['histories'] = ActionHistory::where('user_id', $user->id)
            ->whereType(ActionHistory::TYPE_VOTE)
            ->select('created_at', 'note', 'data')
            ->get();

        $userToken = UserToken::whereUserId($user->id)->whereTokenType('ptt')->first();
        $data['votes'] = $userToken ? $userToken->votes + $userToken->temp_votes : 0;

        return $this->apiResponse($data);
    }


    public function myRanks($campaign_id, $token_type)
    {
        $user = auth()->user();

        $ranks = RentRecord::where('campaign_id', $campaign_id)
            ->where('token_type', $token_type)
            ->whereUserId($user->id)
            ->whereIn('action', [RentRecord::ACTION_JOIN_CAMPAIGN, RentRecord::ACTION_JOIN_TEAM, RentRecord::ACTION_DEDUCTION])
            ->groupBy('team_id')
            ->select('team_id', DB::raw("SUM(token_amount) as total"))
            ->orderBy('total', 'desc')
            ->get();


        $teams = $this->format_list($ranks);
        $rank_ids = array_column($teams, 'ranking_id');

        array_multisort($rank_ids, SORT_ASC, $teams);

        $data['data'] = $teams;
        $data['has_rent'] = $user->getHasRent($campaign_id, $token_type);
        $token = $user->user_token($token_type);
        $data['total_token'] = $token->token_amount + $token->freeze;

        return $this->apiResponse($data);
    }

    public function myVoteRank($campaign_id, $token_type)
    {
        $user = auth()->user();

        $team_ids = TokenVote::whereUserId($user->id)->select(DB::raw('distinct team_id'))->get()->pluck('team_id');

        $ranks = TokenVote::whereIn('team_id', $team_ids)
            ->select('team_id', DB::raw("SUM(amount) as total"))
            ->groupBy('team_id')
            ->orderBy('total', 'desc');

        $data = $this->paginate($ranks, ['campaign_id' => $campaign_id, 'token_type' => $token_type], count($team_ids));

        return $this->apiResponse($data);
    }

    public function tokenDetail(Request $request)
    {
        $user = auth()->user();
        $page = $request->get('page', 1);
        $page_size = $request->get('page_size', 10);

        $records = ActionHistory::whereUserId($user->id)
            ->whereIn('action', [User::ACTION_JOIN_TEAM, User::ACTION_PREPAID])
            ->select('created_at', 'note', 'team_id', 'data')
            ->orderBy('id', 'desc')
            ->skip(($page - 1) * $page_size)
            ->take($page_size)
            ->get();

        foreach ($records as &$record) {
            if ($record->team_id) {
                $team = Team::find($record->team_id);

                $record->data = -$record->data;
                $record->team_name = $team->team_name;
                $record->team_logo = $team->logo;
            }
        }

        return $this->apiResponse($records);
    }

    public function myIncome()
    {
        $user = auth()->user();

        $ranks = RentRecord::where('campaign_id', 1)
            ->where('token_type', 'ptt')
            ->whereUserId($user->id)
            ->whereIn('action', [RentRecord::ACTION_JOIN_CAMPAIGN, RentRecord::ACTION_JOIN_TEAM, RentRecord::ACTION_DEDUCTION])
            ->groupBy('team_id')
            ->select('team_id')
            ->get()->toArray();

        if (!$ranks) {
            $res['max_rank_id'] = null;

            $res['has_rent'] =  0;

            $res['credit'] = 0;

            $res['income'] = 100;
            $res['next_income'] = 1000;
            $res['unit'] = 'CNY';
            return $this->apiResponse($res);
        }

        foreach ($ranks as $rank) {
            $data[$rank['team_id']] = DataCache::getZrank($rank['team_id']);
        }

        $res['max_rank_id'] = min($data);
        $team_id = array_search(min($data), $data);

        $res['has_rent'] = RentRecord::where('campaign_id', 1)
            ->whereIn('action', [RentRecord::ACTION_JOIN_CAMPAIGN, RentRecord::ACTION_JOIN_TEAM, RentRecord::ACTION_DEDUCTION])
            ->where('token_type', 'ptt')
            ->whereUserId($user->id)
            ->whereTeamId($team_id)
            ->sum('token_amount') ?? 0;

        $res['credit'] = $res['has_rent'] * User::CREDIT_TOKEN_RATIO + TokenVote::totalVoteOf($team_id, $user->id) * User::CREDIT_VOTE_RATIO;

        $res['income'] = 100;
        $res['next_income'] = 1000;
        $res['unit'] = 'CNY';

        return $this->apiResponse($res);
    }

    public function incomeDetail()
    {
        $income = [
            [
                'created_at' => '2018-11-08 12:00:00',
                'src' => '帮推客战队',
                'eth_income' => 1,
                'ptt_income' => 200,
            ],
            [
                'created_at' => '2018-11-08 12:00:00',
                'src' => '帮推客战队',
                'eth_income' => 1,
                'ptt_income' => 200,
            ],
            [
                'created_at' => '2018-11-08 12:00:00',
                'src' => '帮推客战队',
                'eth_income' => 1,
                'ptt_income' => 200,
            ],
            [
                'created_at' => '2018-11-08 12:00:00',
                'src' => '帮推客战队',
                'eth_income' => 1,
                'ptt_income' => 200,
            ],
            [
                'created_at' => '2018-11-08 12:00:00',
                'src' => '帮推客战队',
                'eth_income' => 1,
                'ptt_income' => 200,
            ],
            [
                'created_at' => '2018-11-08 12:00:00',
                'src' => '帮推客战队',
                'eth_income' => 1,
                'ptt_income' => 200,
            ],
            [
                'created_at' => '2018-11-08 12:00:00',
                'src' => '帮推客战队',
                'eth_income' => 1,
                'ptt_income' => 200,
            ],
            [
                'created_at' => '2018-11-08 12:00:00',
                'src' => '帮推客战队',
                'eth_income' => 1,
                'ptt_income' => 200,
            ],
            [
                'created_at' => '2018-11-08 12:00:00',
                'src' => '帮推客战队',
                'eth_income' => 1,
                'ptt_income' => 200,
            ],
            [
                'created_at' => '2018-11-08 12:00:00',
                'src' => '帮推客战队',
                'eth_income' => 1,
                'ptt_income' => 200,
            ],
            [
                'created_at' => '2018-11-08 12:00:00',
                'src' => '帮推客战队',
                'eth_income' => 1,
                'ptt_income' => 200,
            ],
        ];

        return $this->apiResponse($income);
    }


    public function getDepositAddress()
    {

        $user = auth()->user();

//        $address = $user->addresses()->where('type', 'super_user')->first();
//
//        if (!$address) {
//
//            $url = config('app.node_domain') . "/account";
//
//            $client = new Client();
//
//            $res = $client->request('POST', $url, [
//                'form_params' => [
//                    'phone' => $user->phone,
//                    'password' => $user->phone . rand(100000, 999999),
//                ],
//            ]);
//
//            $bodys = (string)$res->getBody();
//
//            $result = json_decode($bodys);
//
//            if (isset($result->address)) {
//                try {
//                    DB::beginTransaction();
//
//                    $address = UserAddress::create([
//                        'user_id' => $user->id,
//                        'address' => $result->address,
//                        'address_password' => $result->password,
//                        'type' => UserAddress::SUPER_USER,
//                    ]);
//
//                    DB::commit();
//                } catch (\Exception $e) {
//                    DB::rollBack();
//                    \Log::error('User adress create failed user_id = '. $user->id . '[' .  $e->getMessage() . ']');
//                }
//            } else {
//                return $this->error('地址生成失败, 请联系管理员');
//            }
//        }
//
//        $data['address'] = $address->address;
//        $data['qrcode_str'] = 'ethereum:' . $address->address;

        $data['address']  = '0x923139d93f305Ad6272ae9E80B2467bf1a630673';
        $data['qrcode_str'] = config('app.super_user_url') . "/api/campaign/test/add/token?token_amount=5000&user_id=$user->id";

        return $this->apiResponse($data);
    }
}
