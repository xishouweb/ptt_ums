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
    const SAVING_APPLY_FAILED_STATUS = 4;
    const SAVING_APPLY_SUCCESS_STATUS = 2;
    const SAVING_DEFAULT_AUDIT_STATUS = 3;

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->icon = config('alioss.ossURL') . '/' . $model->icon;
        });
    }

    public function users()
    {
        return $this->belongsTo('App\Models\AdminUser', 'user_id', 'id');
    }
}
