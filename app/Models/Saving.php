<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Saving extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    const TYPE_SAVING = 1;

    const SAVING_ACTIVATED_STATUS = 1;
    const SAVING_UNACTIVATED_STATUS = 0;

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {

            $model->icon = config('alioss.ossURL') . '/' . $model->icon;

        });
    }

    public function users()
    {
        return $this->belongsTo('App\Models\AdminUser', 'user_id', 'id');
    }
}
