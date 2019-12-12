<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWalletTransaction extends Model
{
    use SoftDeletes;
    
    const FAILD_STATUS = 0;

    protected $guarded = ['id'];

}
