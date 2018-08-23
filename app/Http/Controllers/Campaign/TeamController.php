<?php

namespace App\Http\Controllers\Campaign;

use App\Models\Photo;
use App\Models\RentRecord;
use App\Models\Team;
use App\Models\TeamUser;
use App\Services\QrCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return QrCode::getQrCodeUrl("0x923139d93f305Ad6272ae9E80B2467bf1a630673", 600,121);
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
            return $this->apiResponse([], '用户未登录!', 1);
        }
        $requestData = $request->only(['team_name', 'logo', 'info', 'campaign_id', 'token_amount', 'token_type']);
        $photo = Photo::upload($request, 'logo');
        if (!$photo) {
            return $this->apiResponse([], '图片上传失败!', 1);
        }

        try{
            $team = new Team();
            $team->team_name = $requestData['team_name'];
            $team->info = $requestData['info'];
            $team->logo = $photo->url;
            $team->creater_user_id = 1;
            $team->campaign_id = $requestData['campaign_id'];

            $team->save();

            RentRecord::record($user, $team->id, $requestData['token_amount'], $requestData['token_type'], $requestData['campaign_id']);


            return $this->apiResponse($team, '团队创建成功');
        } catch (\Exception $e) {

        }



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

    public function join(Request $request)
    {
        $user = auth()->user();
        $team_id = $request->get('team_id');
        if (!$team_id || !$team = Team::find($team_id)) {
            return $this->apiResponse([], '未找到该团队', 1);
        }

        try{
            $token_type =$request->get('token_type');
            $campaign_id = $request->get('campaign_id');
            $token_amount = $request->get('token_amount');



            DB::beginTransaction();
            $teamUser = new TeamUser();

            $teamUser->user_id = $user->id;
            $teamUser->team_id = $team_id;
            $teamUser->campaign_id = $campaign_id;

            $teamUser->save();

            RentRecord::record($user, $team_id, $token_amount, $token_type, $campaign_id);

            return $this->apiResponse($teamUser, '加入成功');
        } catch (\Exception $e) {

            return $this->apiResponse([], $e->getMessage(), 1);
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

        $ranks = RentRecord::where('campaign_id', $campaign_id)
            ->where('token_type', $token_type)
            ->whereIn('action', [RentRecord::ACTION_JOIN_CAMPAIGN, RentRecord::ACTION_JOIN_TEAM])
            ->groupBy('team_id')
            ->select('team_id', \DB::raw("SUM(token_amount) as total"))
            ->orderBy('total', 'desc');

        $count = \DB::select("select count(1) as total_size from (select team_id, sum(token_amount) as total from rent_records where campaign_id = 1 GROUP BY team_id order by total DESC ) as rank ");

        $data = $this->paginate($ranks, ['campaign_id' => $campaign_id, 'token_type' => $token_type], $count[0]->total_size ?? 0);


        return $this->apiResponse($data);
    }

}
