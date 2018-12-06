<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WechatOpenid extends Model
{
    protected $guarded = ['id'];

    public static function record($openid, $unionid, $channel, $user_id = null, $subscribe = 0)
    {
        return static::create([
            'openid' => $openid,
            'unionid' => $unionid,
            'channel' => $channel,
            'user_id' => $user_id,
            'subscribe' => $subscribe
        ]);
    }
}
