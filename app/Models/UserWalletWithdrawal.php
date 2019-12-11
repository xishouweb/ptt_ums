<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWalletWithdrawal extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    const PENDING_STATUS = 0;
    const COMPLETE_STATUS = 1;
    const PTT_FEE = 100;
}
