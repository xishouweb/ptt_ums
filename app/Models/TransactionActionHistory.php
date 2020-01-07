<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionActionHistory extends Model
{
    protected $guarded = ['id'];

    const STATUS_SUSSESS = 1;
    const STATUS_FAILED = 2;
}
