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
    protected $description = '监测用户持仓情况';

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
            ->get();
        foreach ($savings as $saving) {
            $user_ids = SavingParticipateRecord::where('saving_id', $saving->id)
                ->where('status', SavingParticipateRecord::STATUS_JOIN)
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
                if ($saving_days >= 2) {
                    try {
                        $saving_status = SavingStatus::where('created_at', '>=', date('Y-m-d 00:00:00', strtotime('-1 day')))
                            ->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime('-1 day')))
                            ->first();
                        DB::beginTransaction();
                        // 增加余额
                        $user_wallet = UserWalletBalance::where('user_id', $user_id)->where('symbol', 'ptt')->first();
                        // 奖励金额
                        $award = round($saving_status->total_balance * $saving->rate / 365, 8);
                        $user_wallet->total_balance += $award;
                        $user_wallet->save();
                        // 钱包奖励记录
                        $tran_data = [
                            'user_id' => $user_id,
                            'address' => $user_wallet->address,
                            'symbol'  => UserWalletTransaction::PTT,
                            'type'    => UserWalletTransaction::AWARD_TYPE,
                            'amount'  => $award,
                            'to'      => $user_wallet->address,
                            'rate'    => $saving->rate * 100 . '%'
                        ];
                        UserWalletTransaction::create($tran_data);
                        // 持仓奖励记录
                        $saving_award_data = [
                            'user_id' => $user_id,
                            'saving_id' => $saving->id,
                            'amount' => $user_wallet->total_balance,
                            'award' => $award
                        ];
                        SavingAward::create($saving_award_data);
                        DB::commit();
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
