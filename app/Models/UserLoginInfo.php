<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserLoginInfo extends Model
{
    protected $table = 'user_login_infos';

    protected $guarded = ['id'];
}
