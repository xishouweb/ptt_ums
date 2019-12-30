<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavingAward extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function savings()
    {
        return $this->belongsTo(Saving::class, 'saving_id', 'id');
    }
}
