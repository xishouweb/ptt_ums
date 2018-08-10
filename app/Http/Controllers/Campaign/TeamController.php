<?php

namespace App\Http\Controllers\Campaign;

use App\Models\Photo;
use App\Models\Team;
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
        $a = config('alioss.networkType');
        dd($a);
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

    public function join($team_id){

    }

}
