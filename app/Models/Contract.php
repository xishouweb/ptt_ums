<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;

    const STATUS_NOT_ENABLED = 0;
    const STATUS_ENABLED = 1;

    const TYPE_ETH_CONTRACT = 0;
    const TYPE_PTT_CONTRACT = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     *
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'status', 'verified', 'enabled', '_id', 'address', 'symbol', 'decimals', 'totalSupply', 'name',
    ];
}
