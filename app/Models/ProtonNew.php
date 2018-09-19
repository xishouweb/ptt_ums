<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProtonNew extends Model
{
    use SoftDeletes;

    const STASUS_NOMAL = 1;

    protected $guarded = ['id'];
}
