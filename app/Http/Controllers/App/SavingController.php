<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Captcha;
use App\Models\DataCache;
use App\Models\Saving;
use App\Models\SavingAward;
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

        if ($status == 0) {
            $saving = $saving->where('status', Saving::SAVING_UNACTIVATED_STATUS);
        } else if ($status == 1) {
            $saving = $saving->where('status', Saving::SAVING_ACTIVATED_STATUS);
        } else if ($status == 3 && $user) {
            $join_saving_ids = SavingParticipateRecord::where('user_id', $user->id)
                ->where('status', SavingParticipateRecord::STATUS_JOIN)
                ->pluck('saving_id')
                ->toArray();
            Log::info($user->id);
            Log::info($join_saving_ids);
            $saving = $saving->whereIn('id', $join_saving_ids);
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
        } else {
            foreach ($data['data'] as &$datum) {
                $datum['already_participate'] = false;
            }
        }

        return $this->apiResponse($data);
    }

    // 锁仓活动详情
    public function show(Request $request)
    {
        $id = $request->input('id');
        $lang = $request->input('lang', 'cn');
        $saving = Saving::where('id', $id);
        if ($lang == 'en') {
            $saving->select('id', 'title_en as title', 'rate', 'yield_time', 'started_at', 'ended_at', 'detail_rule_en as detail_rule', 'entry_standard', 'status');
        } else {
            $saving->select('id', 'title', 'rate', 'yield_time', 'started_at', 'ended_at', 'detail_rule', 'entry_standard', 'status');
        }
        $saving = $saving->first();
        if (!$saving) {
            return $this->error('活动不存在');
        }

        $saving->sign_agreement_at = date('Y-m-d H:i:s');
        $saving->yield_effective_at = date('Y-m-d H:i:s', strtotime('+2 day'));
        $saving->already_participate = false;
        $saving->available_amount = 0;
        $saving->awarded = 0;
        $saving->awarded_time = 0;

        if (Auth::check()) {
            $user = Auth::user();
            $saving->awarded = round(SavingAward::where('user_id', $user->id)->where('saving_id', $saving->id)->sum('award'), 6);
            $saving->awarded_time = SavingAward::where('user_id', $user->id)->where('saving_id', $saving->id)->count(['id']);

            $record = SavingParticipateRecord::where('user_id', $user->id)->where('saving_id', $saving->id)->first();
            if ($record) {
                $saving->sign_agreement_at = (string)$record->created_at;
            }
            if ($saving->awarded_time > 0) {
                $saving->yield_effective_at = (string)SavingAward::where('user_id', $user->id)->where('saving_id', $saving->id)->orderBy('id')->first()->created_at;
            } else {
                $saving->yield_effective_at = date('Y-m-d H:i:s', strtotime($saving->sign_agreement_at) + 86400 * 2);
            }

            $saving->already_participate = SavingParticipateRecord::where('user_id', $user->id)->where('saving_id', $saving->id)->where('status', SavingParticipateRecord::STATUS_JOIN)->count(['id']) ? true : false;

            $balance = UserWalletBalance::where('user_id', $user->id)->where('symbol', 'ptt')->first();
            $saving->available_amount = $balance ? round($balance->total_balance, 4) : 0;
        }
        return $this->apiResponse($saving);
    }

    // 锁仓总收益（ptt个数）
    public function yield()
    {
        $user = Auth::user();
        if (!$user) {
            return $this->error();
        }
        $saving_award = SavingAward::where('user_id', $user->id)
            ->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])
            ->first();
        $data['yesterday_yield'] = 0;
        if ($saving_award) {
            $data['yesterday_yield'] = round($saving_award->award, 6);
        }
        $data['total_yield'] = round(SavingAward::where('user_id', $user->id)->sum('award'), 6);
        return $this->apiResponse($data);
    }

    // 锁仓收益历史
    public function yieldHistory(Request $request)
    {
        $user = Auth::user();
        $id = $request->input('id');
        if (!$id || !$user) {
            return $this->error();
        }
        $page_size = $request->input('page_size', 10);
        $data = SavingAward::where('user_id', $user->id)
            ->where('saving_id', $id)
            ->select('id', DB::raw('ROUND(amount, 6) as amount'), 'award', 'created_at')
            ->orderBy('id', 'desc')
            ->paginate($page_size);
        return $this->apiResponse($data);
    }

    // 参加或退出活动
    public function participate(Request $request)
    {
        $user = Auth::user();
        $id = $request->input('id');
        if (!$id || !$user) {
            return $this->error();
        }
        try {
            DB::beginTransaction();
            $record = SavingParticipateRecord::where('user_id', $user->id)->where('saving_id', $id)->first();
            if ($record) {
                if ($record->status == SavingParticipateRecord::STATUS_NOT_JOIN) {
                    $record->status = 1;
                } else {
                    $record->status = 0;
                }
                $record->save();
            } else {
                $data = [
                    'user_id' => $user->id,
                    'saving_id' => $id,
                    'status' => SavingParticipateRecord::STATUS_JOIN
                ];
                SavingParticipateRecord::create($data);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('参加或退出活动error');
            Log::error($e->getMessage());
        }
        return $this->success();
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
