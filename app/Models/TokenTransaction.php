<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenTransaction extends Model
{
    const ACTION_INPUT = 'input';
    const ACTION_OUTPUT = 'output';

    protected $guarded = ['id'];
}
