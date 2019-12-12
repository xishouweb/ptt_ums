<?php

namespace App\Models;

use App\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWalletBalance extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'address', 'symbol', 'locked_balance', 'total_balance'];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
