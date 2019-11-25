<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrackItem extends Model
{

	protected $guarded = ['id'];

	const TYPE_TRACK = 0;
	const TYPE_BUSINESS = 1;

}
