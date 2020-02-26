<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWalletWithdrawal extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    const PENDING_STATUS = 0;
    const COMPLETE_STATUS = 1;
    const FAILED_STATUS = 2;
    const TRANSFERING_STATUS = -1;
    const TRANSFERING_FAILED_STATUS = -2;

    const PTT_FEE = 100;

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getBalanceOf($symbol)
    {
        $balance = UserWalletBalance::whereSymbol($symbol)->whereUserId($this->user_id)->first();

        return $balance ? $balance->total_balance - $balance->locked_balance : 0;
    }

    public function userWalletTransaction()
    {
        return $this->hasOne(UserWalletTransaction::class, 'id', 'user_wallet_transaction_id');
    }

}
