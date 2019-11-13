<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceQueryStatistic extends Model
{
    protected $guarded = ['id'];

    public function user_xu_hosts()
    {
        return $this->belongsTo('App\Models\UserXuHost', 'xu_host_id', 'xu_host_id');
    }
}
