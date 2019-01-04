<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarkSixBetHistory extends Model
{
	use SoftDeletes;

	protected $guarded = ['id'];

	const STATUS_NO_LOTTERY     = 0;    //未开奖
	const STATUS_FIRST_PRIZE    = 1;    //一等奖
    const STATUS_SECOND_PRIZE   = 2;    //二等奖
    const STATUS_THIRD_PRIZE    = 3;    //三等奖
    const STATUS_FOURTH_PRIZE   = 4;    //四等奖
    const STATUS_FIFTH_PRIZE    = 5;    //五等奖
    const STATUS_SIXTH_PRIZE    = 6;    //六等奖
    const STATUS_SEVENTH_PRIZE  = 7;    //七等奖
    const STATUS_LOSING_LOTTERY = 8;    //未中奖
}
