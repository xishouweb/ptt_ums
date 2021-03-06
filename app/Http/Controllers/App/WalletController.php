<?php

namespace App\Http\Controllers\App;

use App\Console\Commands\CheckUserSavingStatus;
use App\Http\Controllers\Controller;
use App\Models\Captcha;
use App\Models\DataCache;
use App\Models\Saving;
use App\Models\SavingParticipateRecord;
use App\Models\Setting;
use App\Models\UserActionHistory;
use App\Models\UserLoginInfo;
use App\Models\UserWalletBalance;
use App\Models\UserWalletTransaction;
use App\Models\UserWalletWithdrawal;
use App\Models\TransactionActionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    // 总资产
    public function total(Request $request)
    {
        $currency = $request->input('currency', 'cny');
        $user = Auth::user();
        if (!$user) {
            return $this->error();
        }
        $balances = UserWalletBalance::where('user_id', $user->id)->get();
        $data = [
            'asset_balance' => 0,
		    'address' => $user->cloud_wallet_address,
		    'list' => [
		        [
                    'symbol'  => 'ptt',
                    'icon'    => 'http://images.proton.global/0x4689a4e169eb39cc9078c0940e21ff1aa8a39b9c.png',
                    'price'   => 0,
                    'amount'  => 0,
                    'balance' => 0,
                ]
            ]
        ];
        try {
            foreach ($balances as $balance) {
                foreach ($data['list'] as &$datum) {
                    if ($balance->symbol == $datum['symbol']) {
                        $datum['amount'] = round($balance->total_balance, 4);
                    }
                }
            }
            foreach ($data['list'] as $datum) {
                if ($datum['amount']) {
                    $price = ToolController::getCurrencyPrice($datum['symbol'], $currency);
                    $datum['price'] = round($price, 6);
                    $datum['balance'] = round($datum['amount'] * $price, 2);
                    $data['asset_balance'] += round($datum['balance'], 2);
                }
            }
        } catch (\Exception $e) {
            Log::error('总资产获取失败');
            Log::error($e->getMessage());
            return $this->error();
        }
        return $this->apiResponse($data);
    }

    // 单币种总资产
    public function symbol(Request $request)
    {
        $symbol = $request->input('symbol');
        $currency = $request->input('currency', 'cny');
        $user = Auth::user();
        if (!$user || !$symbol) {
            return $this->error();
        }
        $balance = UserWalletBalance::where('user_id', $user->id)->where('symbol', $symbol)->first();
        $data = [
            'amount' => 0,
            'asset_balance' => 0,
            'icon'    => '',
        ];
        if ($balance) {
            $price = ToolController::getCurrencyPrice($symbol, $currency);
            $data['amount'] += round($balance->total_balance, 4);
            $data['asset_balance'] += round($balance->total_balance * $price, 2);
            if ($symbol == 'ptt') {
                $data['icon'] = 'http://images.proton.global/0x4689a4e169eb39cc9078c0940e21ff1aa8a39b9c.png';
            }
        }
        return $this->apiResponse($data);
    }

    // 记录列表
    public function transactionList(Request $request)
    {
        $user = Auth::user();
        $page_size = $request->input('page_size', 10);
        $type = $request->input('type', 0);
        $symbol = $request->input('symbol');
        if (!$user || !$symbol) {
            return $this->error();
        }
        $transactions = UserWalletTransaction::where('user_id', $user->id)
            ->select('id', 'user_id', 'symbol', 'type', DB::raw('ROUND(amount, 4) as amount'), 'status', 'created_at', 'completed_at', 'block_confirm', 'rate');
        if ($type) {
            $transactions = $transactions->where('type', $type);
        }
        $data = $transactions->orderBy('id', 'desc')->paginate($page_size);
        return $this->apiResponse($data);
    }

    // 单条记录
    public function transaction(Request $request)
    {
        $user = Auth::user();
        $id = $request->input('id');
        if (!$user || !$id) {
            return $this->error();
        }
        $transaction = UserWalletTransaction::where('id', $id)
            ->where('user_id', $user->id)
            ->select('id', 'user_id', DB::raw('upper(symbol) as symbol'), 'type', 'status', 'block_confirm', 'created_at', 'completed_at', DB::raw('ROUND(amount, 4) as amount'), 'to', 'from', 'fee', 'tx_hash', 'block_number')
            ->first();
        if (!$transaction) {
            return $this->error();
        }
        return $this->apiResponse($transaction);
    }

    // 提现条件
    public function condition(Request $request)
    {
        $symbol = $request->input('symbol');
        $user = Auth::user();
        if (!$user || !$symbol) {
            return $this->error();
        }
        $balance = UserWalletBalance::where('user_id', $user->id)->where('symbol', $symbol)->first();
        $data = [
            'avbl' => $balance ? $balance->total_balance - $balance->locked_balance : 0,
            'transfer_limit' => Setting::retrieve('ptt_transfer_limit', 1000000, 'PTT单笔转账上限', true),
            'daily_transfer_limit' => Setting::retrieve('ptt_daily_transfer_limit', 10000000, 'PTT每日转账上限', true),
            'fee' => Setting::retrieve('ptt_fee', 1000, 'PTT转账手续费', true),
            'already_participate' => false,
            'saving_lower_limit' => 0,
        ];
        $saving = Saving::where('type', Saving::TYPE_SAVING)->where('status', Saving::SAVING_ACTIVATED_STATUS)->first();
        if ($saving) {
            $data['already_participate'] = SavingParticipateRecord::where('user_id', $user->id)
                ->where('saving_id', $saving->id)
                ->where('status', SavingParticipateRecord::STATUS_JOIN)
                ->count(['id']) ? true : false;
            $data['saving_lower_limit'] = $saving->entry_standard;
        }
        return $this->apiResponse($data);
    }

    // 申请提币
    public function withdraw(Request $request)
    {
        // 判断参数
        $user = Auth::user();
        $symbol = $request->input('symbol');
        $address = $request->input('address');
        $amount = $request->input('amount');
        $captcha = $request->input('captcha');
        $password = $request->input('password');
        $device_info = $request->input('device_info');
        if (!$user || !$symbol || !$address || !$amount || !$captcha || !$password || !$device_info || $amount <= 0) {
            return $this->error();
        }
        // 判断提币地址是否为自身
        if ($address == $user->cloud_wallet_address) {
            return $this->error('钱包地址不能相同');
        }
        // 判断24小时内是否修改过密码
        if (DataCache::getAlterPwdLock($user->id)) {
            return $this->error('改密后24小时内不允许转账');
        }
        // 判断余额
        $balance_model = UserWalletBalance::where('user_id', $user->id)->where('symbol', $symbol)->first();
        $fee = Setting::retrieve('ptt_fee', 1000, 'PTT转账手续费', true);
        $available_balance = $balance_model->total_balance - $balance_model->locked_balance - $fee - $amount;
        if ($available_balance < 0) {
            return $this->error('余额不足');
        }
        // 判断单次提款金额
        $transfer_limit = Setting::retrieve('ptt_transfer_limit', 1000000, 'PTT单笔转账上限', true);
        if ($transfer_limit < $amount) {
            return $this->error('超过单次提款金额上限');
        }
        // 判断今日提款金额
        $daily_transfer_limit = Setting::retrieve('ptt_daily_transfer_limit', 10000000, 'PTT每日转账上限', true);
        $today_transfer_amount = UserWalletWithdrawal::where('user_id', $user->id)
            ->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])
            ->sum('amount') + $amount;
        if ($daily_transfer_limit < $today_transfer_amount) {
            return $this->error('超过今日提款金额上限');
        }
        // 验证码
        $valid_captcha = Captcha::valid($user->phone, $captcha);
        if (!$valid_captcha) {
            return $this->error('验证码错误');
        }
        // 交易密码
        $hash_result = Hash::check($password, $user->trade_password);
        if (!$hash_result) {
            return $this->error('交易密码错误');
        }
        try {
            DB::beginTransaction();

            // 创建transaction记录
            $t_data = [
                'user_id' => $user->id,
                'address' => $user->cloud_wallet_address,
                'symbol' => $symbol,
                'type' => UserWalletTransaction::OUT_TYPE,
                'status' => UserWalletTransaction::OUT_STATUS_PADDING,
                'amount' => -$amount,
                'to' => $address,
                'fee' => $fee,
                'remark' => -$amount - $fee
            ];
            $transaction = UserWalletTransaction::create($t_data);

            // 创建提币记录
            $w_data = [
                'user_id' => $user->id,
                'symbol' => $symbol,
                'status' => UserWalletWithdrawal::PENDING_STATUS,
                'amount' => $amount,
                'to' => $address,
                'fee' => $fee,
                'device_info' => $device_info,
                'user_wallet_transaction_id' => $transaction->id,
                'ptt_balance' => $available_balance
            ];
            $withdrawal = UserWalletWithdrawal::create($w_data);

            // 增加锁定金额
            $balance_model->locked_balance += $amount + $fee;
            $balance_model->save();

            // 记录行为
            UserActionHistory::record($user->id, UserActionHistory::TYPE_OUT, $transaction->id, $withdrawal->id);

            DB::commit();

            $phones = [
                'chennan' => 18612010683,
                'adam' => 15712896282,
            ];

            $message = '有一笔新的提币申请, 请及时处理';
            foreach ($phones as $key => $mobile) {
                \App\Services\SMSSender::send($mobile, $message);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('申请提币失败');
            Log::error($e->getMessage());
            return $this->error();
        }
        // 查询持仓情况
        $saving = Saving::where('type', Saving::TYPE_SAVING)
            ->where('status', Saving::SAVING_ACTIVATED_STATUS)
            ->where('started_at', '<=', date('Y-m-d H:i:s'))
            ->where('ended_at', '>=', date('Y-m-d H:i:s'))
            ->first();
        if ($saving) {
            $join_record = SavingParticipateRecord::where('user_id', $user->id)
                ->where('saving_id', $saving->id)
                ->where('status', SavingParticipateRecord::STATUS_JOIN)
                ->count();
            if ($join_record) {
                CheckUserSavingStatus::checkUserSavingStatus($user->id, $saving);
            }
        }
        return $this->success();
    }

    public function checkTradePwd(Request $request)
    {
        $user = Auth::user();
        $trade_password = $request->input('password');
        $result = Hash::check($trade_password, $user->trade_password);
        if (!$result) {
            return $this->error('交易密码错误');
        }
        return $this->success();
    }

    public function nodeTxCallback(Request $request)
    {
        \Log::info('all request = ', [$request->all()]);
        $ums_tx_id =  $request->get('ums_tx_id');
        $gas_price =  $request->get('gas_price');
        $type = $request->get('type');
        $block = $request->get('data');

        \Log::info('node_tx_callback tx_id =====> ' . $ums_tx_id);

        $record = TransactionActionHistory::whereTxId($ums_tx_id)->whereSymbol('ptt')->whereStatus(TransactionActionHistory::STATUS_PADDING)->first();
        if (!$record) {
            \Log::error('未找到该TransactionActionHistory记录', [$request->all()]);
            return;
        }

        if ($type == 'error') {
            $withdrawal = UserWalletWithdrawal::whereUserWalletTransactionId($ums_tx_id)->first();
            $withdrawal->status = UserWalletWithdrawal::TRANSFERING_FAILED_STATUS;
            $withdrawal->save();

            $record->payload = $block;
            $record->type = 'transfor_error';
            $record->save();

            return $this->success();
        }

        try {

            if(!$block['status']) throw new Exception("转账失败");
            DB::beginTransaction();

            $record->fee = $block['gasUsed'] * $gas_price / 1000000000000000000;
            $record->status = TransactionActionHistory::STATUS_SUSSESS;
            $record->tx_hash = $block['transactionHash'];
            $record->block_number = $block['blockNumber'];
            $record->payload = json_encode($block);
            $record->save();

            if ($record->type == 'send') {

                $tx = UserWalletTransaction::find($ums_tx_id);
                $withdrawal = UserWalletWithdrawal::whereUserWalletTransactionId($ums_tx_id)->first();
                $balance = UserWalletBalance::whereUserId($tx->user_id)->whereSymbol($tx->symbol)->first();


                $spending = $tx->fee + abs($tx->amount);
                $balance->locked_balance -= $spending;
                $balance->total_balance -= $spending;
                $balance->save();

                $withdrawal->status = UserWalletWithdrawal::COMPLETE_STATUS;
                $withdrawal->from = $block['from'];
                $withdrawal->save();

                $tx->status = UserWalletTransaction::OUT_STATUS_TRANSFER;
                $tx->tx_hash = $block['transactionHash'];
                $tx->from = $block['from'];
                $tx->completed_at = date('Y-m-d H:i:s');
                $tx->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('node_tx_callback处理失败id = '. $record->id .' ***********> ', [$e->getMessage()]);
            TransactionActionHistory::create([
                'user_id' => $record->user_id,
                'symbol' => 'ptt',
                'status' => TransactionActionHistory::STATUS_FAILED,
                'type' => 'callback',
                'from' => $block['from'],
                'to' => $block['to'],
                'payload' => json_encode($request->all()),
            ]);
        }

        return $this->success();
    }

    public function saveLoginInfo(Request $request)
    {
        $user = Auth::user();
        $info = $request->input('device_info');
        $device_name = $request->header('devicename', null);
        if (!$user || !$info || !$device_name) {
            return $this->error();
        }
        try {
            DB::beginTransaction();
            $data['user_id'] = $user->id;
            $data['device_name'] = $device_name;
            $data['info'] = $info;
            $data['ip'] = $request->ip();
            $data['user_agent'] = $request->userAgent();
            UserLoginInfo::create($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('记录登录信息');
            Log::error($e->getMessage());
            return $this->error();
        }
        return $this->success();
    }
}
