<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use SoftDeletes;

    const NOT_ENABLED = 0;
    const ENABLED = 1;

    const TYPE_WEB = 0;
    const TYPE_APP = 1;
    const TYPE_TEXT = [
        self::TYPE_WEB => 'Web页面',
        self::TYPE_APP => 'App页面',
    ];

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
        'title', 'status', 'type', 'content', 'image', 'sort',
    ];
}
