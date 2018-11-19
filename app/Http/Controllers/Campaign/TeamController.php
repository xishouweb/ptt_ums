<?php

namespace App\Http\Controllers\Campaign;

use App\Models\ActionHistory;
use App\Models\DataCache;
use App\Models\RentRecord;
use App\Models\Team;
use App\Models\TeamUser;
use App\Models\TokenVote;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $team_name = $request->get('team_name');
        $campaign_id = $request->get('campaign_id');
        $token_type = $request->get('token_type');
        $page = $request->get('page', 1);
        $page_size = $request->get('page_size', 10);

        if (!$token_type || !$campaign_id || !$team_name) {
            return $this->error('参数错误');
        }

        $teams = Team::where('team_name', 'like', '%' . $team_name .'%')
            ->skip(($page - 1) * $page_size)
            ->take($page_size)
            ->get();

        $count = Team::where('team_name', 'like', '%' . $team_name .'%')->count();

        if (!$teams) {
            return $this->apiResponse();
        }

        $teams =  $this->format_list($teams);

        foreach ($teams as &$team) {
            $team['token_amount'] =  RentRecord::where('campaign_id', $campaign_id)
                ->where('token_type', $token_type)
                ->where('team_id', $team['team_id'])
                ->whereIn('action', [RentRecord::ACTION_JOIN_CAMPAIGN, RentRecord::ACTION_JOIN_TEAM, RentRecord::ACTION_DEDUCTION])
                ->sum('token_amount') ?? 0;

            $team['ranking_id'] = DataCache::getZrank($team['team_id']);

            $team['credit'] =  $team['token_amount'] * User::CREDIT_TOKEN_RATIO + TokenVote::totalVoteOf($team['team_id']) * User::CREDIT_VOTE_RATIO;
        }

        $data['data'] = $teams;
        $data['page'] = $page;
        $data['page_size'] =$page_size;
        $data['total_size'] = $count;


        return $this->apiResponse($data);
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
        $user = auth()->user();

        if (!$user) {
            return $this->error( '用户未登录!');
        }
        $requestData = $request->only(['team_name', 'logo', 'info', 'campaign_id', 'token_amount', 'token_type']);
        
        if (!$requestData['team_name']) {
            return $this->error('请填写团队名称');
        }
        if (!$requestData['logo']) {
            return $this->error('请上传团队logo');
        }
        if (!$requestData['info']) {
            return $this->error('请填写团队描述');
        }
        if (!(float)$requestData['token_amount']) {
            return $this->error('锁仓额度不能为空');
        }
        if (!$requestData['team_name']) {
            return $this->error('请填写团队名称');
        }

        $exists = Team::where('creater_user_id', $user->id)->first();

        if ($exists) {
            return $this->error('每人只能创建一个战队');
        }

        $is_exist = Team::whereTeamName($requestData['team_name'])->first();
        if ($is_exist) {
            return $this->error('该名字已被占用~, 请换一个吧');
        }

        try{
            DB::beginTransaction();

            $team = new Team();
            $team->team_name = $requestData['team_name'];
            $team->info = $requestData['info'];
            $team->logo = $requestData['logo'];
            $team->creater_user_id = $user->id;
            $team->campaign_id = $requestData['campaign_id'];

            $team->save();

            RentRecord::record($user, $team->id, $requestData['token_amount'], $requestData['token_type'], $requestData['campaign_id']);

            TokenVote::record($team->id, $user->id, 0);

            DataCache::zAddIntoCreditRank($team->id, $requestData['token_amount'] * User::CREDIT_TOKEN_RATIO);

            DB::commit();

            return $this->apiResponse($team, '团队创建成功');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            return $this->error($e->getMessage());
        }



    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $campaign_id = $request->get('campaign_id');
        $token_type = $request->get('token_type');

        if (!$token_type || !$campaign_id) {
            return $this->error('参数错误');
        }


        $team = Team::find($id);

        if (!$team) {
            return $this->error('未找到团队信息');
        }

        return $this->apiResponse($team->format(['campaign_id' => $campaign_id, 'token_type' => $token_type]));
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

    public function join(Request $request, $team_id)
    {
        $user = auth()->user();

        if (!$team_id || !$team = Team::find($team_id)) {
            return $this->error('未找到该团队');
        }

        $token_type =$request->get('token_type');
        $campaign_id = $request->get('campaign_id');
        $token_amount = (float)$request->get('token_amount');

        if (!$token_type || !$campaign_id || !$token_amount) {
            return $this->error('参数错误');
        }

        try{
            DB::beginTransaction();

            $is_joined = TeamUser::whereUserId($user->id)->whereTeamId($team_id)->count();

            if ($is_joined <= 0){

                $teamUser = new TeamUser();

                $teamUser->user_id = $user->id;
                $teamUser->team_id = $team_id;
                $teamUser->campaign_id = $campaign_id;

                $teamUser->save();
                ActionHistory::record($user->id, User::ACTION_JOIN_TEAM, $team_id, 0, '加入战队');
            }

            RentRecord::record($user, $team_id, $token_amount, $token_type, $campaign_id);

            DataCache::zincrOfCreditRankFor($team_id, $token_amount * User::CREDIT_TOKEN_RATIO);
            DataCache::zincrOfCreditRankFor('self_in_' . $user->id, -($token_amount * User::CREDIT_TOKEN_RATIO));

            ActionHistory::record($user->id, User::ACTION_INCR_TOKEN, $team_id, $token_amount, '往战队增加token', ActionHistory::TYPE_TOKEN);

            DB::commit();
            return $this->apiResponse([], '加入成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
        }
    }

    public function ranks(Request $request)
    {
        if(!$campaign_id = $request->get('campaign_id')){
            return $this->apiResponse([], '请输入活动ID');
        }

        if(!$token_type = $request->get('token_type')){
            return $this->apiResponse([], '未找到token类型');
        }

        $page = (int)$request->get('page');
        $limit = (int)$request->get('page_size');

        $data['total_size'] = DataCache::getCountOfCreditRank();
        $start = ($page - 1) * $limit;
        $end = $page * $limit - 1;

        if ($end > $data['total_size']) {
            $end = $data['total_size'];
        }

        $team_ids = DataCache::getRangOfCreditRank($start, $end);

        $teams = RentRecord::where('campaign_id', $campaign_id)
            ->where('token_type', $token_type)
            ->whereIn('team_id', $team_ids)
            ->whereIn('action', [RentRecord::ACTION_JOIN_CAMPAIGN, RentRecord::ACTION_JOIN_TEAM, RentRecord::ACTION_DEDUCTION])
            ->groupBy('team_id')
            ->select('team_id', \DB::raw("SUM(token_amount) as total"))
            ->orderBy('total', 'desc')
            ->get();
        $teams = $this->format_list($teams);
        $rank_ids = array_column($teams, 'ranking_id');

        array_multisort($rank_ids, SORT_ASC, $teams);

        $data['data'] = $teams;
        $data['page'] = $request->get('page');
        $data['page_size'] = $request->get('page_size');

        return $this->apiResponse($data);
    }

    public function voteRank(Request $request)
    {
        if(!$campaign_id = $request->get('campaign_id')){
            return $this->apiResponse([], '请输入活动ID');
        }

        if(!$token_type = $request->get('token_type')){
            return $this->apiResponse([], '未找到token类型');
        }

        $ranks = TokenVote::groupBy('team_id')
            ->select('team_id', DB::raw("SUM(amount) as total"))
            ->orderBy('total', 'desc');

        $query = "(select team_id, sum(amount) as total from token_votes";

        $team_name = $request->get('team_name');
        if ($team_name) {
            $ids = Team::where('team_name', 'like', "%$team_name%")->get()->pluck('id')->toArray();

            $ranks = $ranks->whereIn('team_id', $ids);

            $query .= "where team_id in $ids";
        }

        $query .= "GROUP BY team_id order by total DESC)";
        $count = DB::select("select count(1) as total_size from $query as vote_rank ");


        $data = $this->paginate($ranks, ['campaign_id' => $campaign_id, 'token_type' => $token_type], $count[0]->total_size ?? 0);

        return $this->apiResponse($data);
    }

}
