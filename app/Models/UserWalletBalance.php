<?php

namespace App\Models;

use App\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWalletBalance extends Model
{
    use SoftDeletes;

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
