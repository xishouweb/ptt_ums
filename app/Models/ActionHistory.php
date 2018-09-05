<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionHistory extends Model
{

    protected $guarded = ['id'];

    public static function record($user_id, $type = null, $action = null, $data = null, $note = null, $payload = null, $count_flag = 0)
    {
        return static::create([
            'user_id' => $user_id,
            'type' => $type,
            'action' => $action,
            'data' => $data,
            'note' => $note,
            'payload' => $payload,
            'count_flag' => $count_flag,
        ]);
    }
}
