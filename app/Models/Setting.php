<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function retrieve($key, $default = null, $createIfNotExist = false)
    {
        $model = self::where('key', $key)->first();
        if (!$model) {
            if ($createIfNotExist) {
                self::create([
                    'key'   => $key,
                    'value' => $default,
                ]);
            }
            return $default;
        }
        return $model->value;
    }
}
