<?php

namespace App\Models;

use App\User;

use Illuminate\Database\Eloquent\Model;

class UserWalletBalance extends Model
{
    //
    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
