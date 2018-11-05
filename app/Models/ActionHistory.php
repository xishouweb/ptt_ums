<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionHistory extends Model
{

    const ACTION_VOTE = 'vote';
    const TYPE_USER = 'user';

    protected $guarded = ['id'];

    public static function record($user_id, $action = null, $team_id = null, $data = null, $note = null, $payload = null, $type = null, $count_flag = 0)
    {
        return static::create([
            'user_id' => $user_id,
            'type' => $type,
            'action' => $action,
            'data' => $data,
            'team_id' => $team_id,
            'note' => $note,
            'payload' => $payload,
            'count_flag' => $count_flag,
        ]);
    }
}
