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

class WalletController extends Controller
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
                    'symbol' => 'ptt',
                    'amount' => 0,
                    'icon' => 'http://images.proton.global/0x4689a4e169eb39cc9078c0940e21ff1aa8a39b9c.png'
                ]
            ]
        ];
        try {
            foreach ($balances as $balance) {
                foreach ($data['list'] as &$datum) {
                    if ($balance->symbol == $datum['symbol']) {
                        $datum['amount'] = $balance->total_balance;
                    }
                }
            }
            foreach ($data['list'] as $datum) {
                if ($datum['amount']) {
                    $price = ToolController::getCurrencyPrice($datum['symbol'], $currency);
                    $data['asset_balance'] += $datum['amount'] * $price;
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
            'asset_balance' => 0
        ];
        if ($balance) {
            $price = ToolController::getCurrencyPrice($symbol, $currency);
            $data['amount'] += $balance->total_balance;
            $data['asset_balance'] += $balance->total_balance * $price;
        }
        return $this->apiResponse($data);
    }

    // 记录列表
    public function transactionList(Request $request)
    {
        $user = Auth::user();
        $type = $request->input('type', 0);
        $symbol = $request->input('symbol');
        if (!$user || !$symbol) {
            return $this->error();
        }
        $transactions = UserWalletTransaction::where('user_id', $user->id)
            ->select('id', 'user_id', 'symbol', 'type', 'amount', 'status', 'created_at', 'block_confirm', 'rate');
        if ($type) {
            $transactions = $transactions->where('type', $type);
        }
        $data = $transactions->orderBy('id', 'desc')->paginate(10);
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
            ->select('id', 'user_id', 'symbol', 'type', 'status', 'block_confirm', 'created_at', 'completed_at', 'amount', 'to', 'from', 'fee', 'tx_hash', 'block_number')
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
            'transfer_limit' => 1000000,
            'daily_transfer_limit' => 10000000,
            'fee' => 100,
        ];
        return $this->apiResponse($data);
    }

    // 申请提币
    public function withdraw(Request $request)
    {
        $user = Auth::user();
        $symbol = $request->input('symbol');
        $address = $request->input('address');
        $amount = $request->input('amount');
        $captcha = $request->input('captcha');
        $password = $request->input('password');
        $device_info = $request->input('device_info');
        if (!$user || !$symbol || !$address || !$amount || !$captcha || !$password || !$device_info) {
            return $this->error();
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
            $t_data = [
                'user_id' => $user->id,
                'address' => $user->cloud_wallet_address,
                'symbol' => $symbol,
                'type' => UserWalletTransaction::OUT_TYPE,
                'status' => UserWalletTransaction::OUT_STATUS_PADDING,
                'amount' => $amount,
                'to' => $address,
                'fee' => UserWalletWithdrawal::PTT_FEE
            ];
            $transaction = UserWalletTransaction::create($t_data);
            $w_data = [
                'user_id' => $user->id,
                'symbol' => $symbol,
                'status' => UserWalletWithdrawal::PENDING_STATUS,
                'amount' => $amount,
                'to' => $address,
                'fee' => UserWalletWithdrawal::PTT_FEE,
                'device_info' => $device_info,
                'user_wallet_transaction_id' => $transaction->id
            ];
            UserWalletWithdrawal::create($w_data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('申请提币失败');
            Log::error($e->getMessage());
            return $this->error();
        }
        return $this->success();
    }
}
