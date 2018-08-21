<?php

namespace App\Models;


class Team extends BaseModel implements FormatInterface
{
    public function format($source = [])
    {
        $data['title'] = $this->title;

        return $data;
    }
}
