<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{

    public function format_list($data, $from = [])
    {
        $result = [];
        foreach($data as $d) {
            $result[] = $d->format($from);
        }
        return $result;
    }

}
