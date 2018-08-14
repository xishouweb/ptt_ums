<?php

namespace App\Http\Controllers\Campaign;

use App\Models\Photo;
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
        $model = $request->only(['team_name', 'logo', 'info', 'campaign_id']);
        $photo = Photo::upload($request, 'logo');
        if (!$photo) {
            return $this->apiResponse([], '图片上传失败!', 1);
        }

        $team = new Team();
        $team->team_name = $model['team_name'];
        $team->info = $model['info'];
        $team->logo = $photo->url;
        $team->creater_user_id = 1;
        $team->campaign_id = $model['campaign_id'];

        $team->save();

        return $this->apiResponse($team, '团队创建成功');

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

    public function join(Request $request, $team_id)
    {
        $user = auth()->user();

        if (!$team = Team::find($team_id)) {
            return $this->apiResponse([], '未找到该团队', 1);
        }

        $teamUser = new TeamUser();

        $teamUser->user_id = $user->id;
        $teamUser->team_id = $team_id;
        $teamUser->campaign_id = $request->get('campaign_id');

        $teamUser->save();

        return $this->apiResponse($teamUser, '加入成功');
    }

}
