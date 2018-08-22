<?php

namespace App\Models;


class Team extends BaseModel implements FormatInterface
{
    public function format($source = [])
    {
        $data['team_name'] = $this->team_name;
        $data['logo'] = $this->logo;
        $data['info'] = $this->info;

        if (isset($source['campaign_id']) && isset($source['token_type'])) {
            $rank =  RentRecord::ranking($source['campaign_id'], $source['token_type'], $this->id);
            $data['ranking_id'] = $rank['ranking_id'];
            $data['credit'] = $rank['total'] * 1;
        }


        return $data;
    }
}
