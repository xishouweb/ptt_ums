<?php

namespace App\Console\Commands;

use App\Http\Controllers\App\ToolController;
use App\Models\Saving;
use App\Models\SavingAward;
use App\Models\SavingParticipateRecord;
use App\Models\SavingStatus;
use App\Models\UserWalletBalance;
use App\Models\UserWalletTransaction;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SavingIssueReward extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ptt:saving_issue_reward';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发放持仓奖励';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('发放持仓奖励');
        $savings = Saving::where('type', Saving::TYPE_SAVING)
            ->where('status', Saving::SAVING_ACTIVATED_STATUS)
            ->where('started_at', '<=', date('Y-m-d H:i:s'))
            ->where('ended_at', '>=', date('Y-m-d H:i:s'))
            ->get();
        foreach ($savings as $saving) {
            $user_ids = SavingParticipateRecord::where('saving_id', $saving->id)
                ->where('status', SavingParticipateRecord::STATUS_JOIN)
                ->orderBy('user_id', 'desc')
                ->pluck('user_id')
                ->toArray();
            foreach ($user_ids as $user_id) {
                $saving_days = SavingStatus::where('created_at', '>=', date('Y-m-d 00:00:00', strtotime('-2 day')))
                    ->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime('-1 day')))
                    ->where('status', SavingStatus::STATUS_ENOUGH)
                    ->where('user_id', $user_id)
                    ->where('saving_id', $saving->id)
                    ->count(['id']);
                Log::info('user_id = ' . $user_id . ' days = ' . $saving_days);
                if ($saving_days >= Saving::SAVING_ISSUE_REWARD_DAYS) {
                    try {
                        $saving_status = SavingStatus::where('created_at', '>=', date('Y-m-d 00:00:00', strtotime('-1 day')))
                            ->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime('-1 day')))
                            ->first();
                        DB::beginTransaction();
                        $user_wallet = UserWalletBalance::where('user_id', $user_id)->where('symbol', 'ptt')->first();
                        // 奖励金额
                        $days = date('L') == 1 ? 366 : 365;
                        $award = round($saving_status->total_balance * $saving->rate / $days, 8);
                        $is_exist_tran = UserWalletTransaction::where('created_at', '>=', date('Y-m-d 00:00:00'))
                            ->where('created_at', '<=', date('Y-m-d 23:59:59'))
                            ->where('user_id', $user_id)
                            ->where('type', UserWalletTransaction::AWARD_TYPE)
                            ->count(['id']);
                        $is_exist_award = SavingAward::where('created_at', '>=', date('Y-m-d 00:00:00'))
                            ->where('created_at', '<=', date('Y-m-d 23:59:59'))
                            ->where('user_id', $user_id)
                            ->where('saving_id', $saving->id)
                            ->count(['id']);
                        if ($is_exist_tran || $is_exist_award) {
                            throw new \Exception('发放持仓奖励失败，记录已存在');
                        }
                        // 钱包奖励记录
                        $tran_data = [
                            'user_id'   => $user_id,
                            'address'   => $user_wallet->address,
                            'symbol'    => UserWalletTransaction::PTT,
                            'type'      => UserWalletTransaction::AWARD_TYPE,
                            'amount'    => $award,
                            'to'        => $user_wallet->address,
                            'rate'      => $saving->rate * 100 . '%'
                        ];
                        UserWalletTransaction::create($tran_data);
                        // 持仓奖励记录
                        $saving_award_data = [
                            'user_id'   => $user_id,
                            'saving_id' => $saving->id,
                            'amount'    => round($user_wallet->total_balance + $award, 8),
                            'award'     => $award
                        ];
                        SavingAward::create($saving_award_data);
                        // 增加余额
                        $user_wallet->total_balance = round($user_wallet->total_balance + $award, 8);
                        $user_wallet->save();
                        DB::commit();
                        Log::info('持仓奖励已发放，user_id = ' . $user_id);
                    } catch (\Exception $e) {
                        Log::error('发放持仓奖励失败，user_id = ' . $user_id);
                        Log::error($e->getMessage());
                        DB::rollBack();
                    }
                }
            }
        }
    }
}
