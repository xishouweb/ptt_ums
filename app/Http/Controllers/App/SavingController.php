<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Captcha;
use App\Models\DataCache;
use App\Models\Saving;
use App\Models\SavingParticipateRecord;
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
    // 锁仓活动列表
    public function index(Request $request)
    {
        $user = Auth::user();
        $lang = $request->input('lang', 'cn');
        $status = $request->input('status', 2);
        $page_size = $request->input('page_size', 10);
        $saving = Saving::where('type', Saving::TYPE_SAVING);
        if ($status === 0) {
            $saving = $saving->where('status', Saving::SAVING_UNACTIVATED_STATUS);
        } else if ($status === 1) {
            $saving = $saving->where('status', Saving::SAVING_ACTIVATED_STATUS);
        }
        if ($lang == 'en') {
            $saving->select('id', 'title_en as title', 'icon', 'yield_time', 'started_at', 'ended_at', 'rate', 'status');
        } else {
            $saving->select('id', 'title', 'icon', 'yield_time', 'started_at', 'ended_at', 'rate', 'status');
        }
        $data = $saving->orderBy('id', 'desc')->paginate($page_size)->toArray();
        if ($user) {
            foreach ($data['data'] as &$datum) {
                $datum['already_participate'] = SavingParticipateRecord::where('user_id', $user->id)->where('saving_id', $datum['id'])->where('status', SavingParticipateRecord::STATUS_JOIN)->count(['id']) ? true : false;
            }
        }
        return $this->apiResponse($data);
    }

    // 锁仓活动详情
    public function show(Request $request)
    {

    }

    // 锁仓总收益（ptt个数）
    public function yield()
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
