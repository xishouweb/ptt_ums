<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavingStatus extends Model
{
    use SoftDeletes;

    protected $table = 'saving_statuses';

    protected $guarded = ['id'];

    const STATUS_ENOUGH = 1;
    const STATUS_NOT_ENOUGH = 0;
}
