<?php

namespace App\Models;


class Team extends BaseModel implements FormatInterface
{
    public function format($source = [])
    {
        $data['team_id'] = $this->id;
        $data['team_name'] = $this->team_name;
        $data['logo'] = $this->logo;
        $data['info'] = $this->info;
        $data['type'] = 'team';

        return $data;
    }
}
