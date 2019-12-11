<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWalletTransaction extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    const IN_TYPE = 1;
    const OUT_TYPE = 2;
    const AWARD_TYPE = 3;

    const PTT = 'ptt';
}
