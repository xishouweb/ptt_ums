<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLogin extends Model
{
    protected $guarded = ['id'];


    public static function record($user, $ip = null, $login_src = null, $ua = null, $remark = null)
    {
        $last_login = static::where('user_id', $user->id)->orderBy('id', 'desc')->first();

        $login = new self();

        $login->user_id = $user->id;
        $login->ip = $ip;
        $login->login_src = $login_src;
        $login->user_agent = $ua;
        $login->remark = $remark;


        $start = date('Y-m-d 00:00:00', strtotime('-1 day'));
        $end = date('Y-m-d 23:59:59', strtotime('-1 day'));
        if ($last_login && $last_login->created_at >= $start && $last_login->created_at <= $end && !$user->checkTodayLogin()) {
            $login->consecutive_days = $last_login->consecutive_days + 1;
        } elseif ($last_login && $user->checkTodayLogin()) {
            $login->consecutive_days = $last_login->consecutive_days;
        }

        $login->save();

        return $login;
    }
}
