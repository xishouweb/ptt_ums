<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function retrieve($key, $value = null, $name = '', $createIfNotExist = false)
    {
        $model = self::where('key', $key)->first();
        if (!$model) {
            if ($createIfNotExist) {
                self::create([
                    'name'  => $name,
                    'key'   => $key,
                    'value' => $value,
                ]);
            }
            return $value;
        }
        return $model->value;
    }
}
