<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Captcha;
use App\Models\DataCache;
use App\Models\UserWalletBalance;
use App\Models\UserWalletTransaction;
use App\Models\UserWalletWithdrawal;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SavingController extends Controller
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
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
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
    public function update(Request $request)
	{

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

    // 锁仓总收益（ptt个数）
    public function yield()
    {

    }

    // 锁仓活动列表
    public function list(Request $request)
    {

    }

    // 锁仓活动详情
    public function detail(Request $request)
    {

    }

    // 锁仓收益历史
    public function yieldHistory(Request $request)
    {

    }

    // 参加或退出活动
    public function participate(Request $request)
    {

    }

    // 风险告知书
    public function riskStatement(Request $request)
    {
        $lang = $request->input('lang', 'cn');
        if ($lang == 'en') {
            $data['title'] = config('riskstatement.en.title');
            $data['content'] = config('riskstatement.en.content');
        } else {
            $data['title'] = config('riskstatement.cn.title');
            $data['content'] = config('riskstatement.cn.content');
        }
        return $this->apiResponse($data);
    }
}
