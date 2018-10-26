<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemJson extends Model
{

	protected $guarded = ['id'];

	protected $table = 'item_json';
}
