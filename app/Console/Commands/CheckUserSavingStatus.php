<?php

namespace App\Console\Commands;

use App\Http\Controllers\App\ToolController;
use App\Models\Saving;
use App\Models\SavingParticipateRecord;
use App\Models\SavingStatus;
use App\Models\UserWalletBalance;
use App\Models\UserWalletTransaction;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckUserSavingStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ptt:check_user_saving_status';

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
        Log::info('监测用户持仓情况');
        $savings = Saving::where('type', Saving::TYPE_SAVING)
            ->where('status', Saving::SAVING_ACTIVATED_STATUS)
            ->where('started_at', '<=', date('Y-m-d H:i:s'))
            ->where('ended_at', '>=', date('Y-m-d H:i:s'))
            ->select('id', 'entry_standard')
            ->get();
        foreach ($savings as $saving) {
            $user_ids = SavingParticipateRecord::where('saving_id', $saving->id)
                ->where('status', SavingParticipateRecord::STATUS_JOIN)
                ->pluck('user_id')
                ->toArray();
            foreach ($user_ids as $user_id) {
                self::checkUserSavingStatus($user_id, $saving);
            }
        }
    }

    public static function checkUserSavingStatus($user_id, $saving) {
        $user_wallet = UserWalletBalance::where('user_id', $user_id)->where('symbol', 'ptt')->first();
        $last_record = SavingStatus::where('user_id', $user_id)
            ->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])
            ->first();
        try {
            DB::beginTransaction();
            $balance = $user_wallet->total_balance - $user_wallet->locked_balance;
            if (!$last_record) {
                $data = [
                    'user_id' => $user_id,
                    'saving_id' => $saving->id,
                    'status' => SavingStatus::STATUS_NOT_ENOUGH,
                    'total_balance' => $balance
                ];
                if ($user_wallet && $balance >= $saving->entry_standard) {
                    $data['status'] = SavingStatus::STATUS_ENOUGH;
                }
                SavingStatus::create($data);
            } else {
                if ($balance < $last_record->total_balance) {
                    $last_record->total_balance = $balance;
                    if ($balance < $saving->entry_standard) {
                        $last_record->status = SavingStatus::STATUS_NOT_ENOUGH;
                    }
                    $last_record->save();
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('验仓出错，$user_id = ' . $user_id);
            Log::error($e->getMessage());
        }
    }
}
