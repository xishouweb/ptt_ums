<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavingParticipateRecord extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    const STATUS_JOIN = 1;
    const STATUS_NOT_JOIN = 0;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function savingAwards()
    {
        return SavingAward::whereUserId($this->user_id)->whereSavingId($this->saving_id)->get();
    }
}
