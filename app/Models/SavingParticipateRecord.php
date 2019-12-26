<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavingParticipateRecord extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    const STATUS_JOIN = 1;
    const STATUS_NOT_JOIN = 0;
}
