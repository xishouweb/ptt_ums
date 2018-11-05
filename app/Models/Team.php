<?php

namespace App\Models;


class Team extends BaseModel implements FormatInterface
{
    public function format($source = [])
    {
        $data['tean_id'] = $this->id;
        $data['team_name'] = $this->team_name;
        $data['logo'] = $this->logo;
        $data['info'] = $this->info;
        $data['type'] = 'team';

        if (isset($source['campaign_id']) && isset($source['token_type'])) {
            $rank =  RentRecord::ranking($source['campaign_id'], $source['token_type'], $this->id);

            if ($rank) {
                $data['ranking_id'] = $rank['ranking_id'];
                $data['credit'] = $rank['total'] * 1;

                $old_model = DataCache::getRanking($this->id);
                $data['status'] = $rank['ranking_id'] >= $old_model['ranking_id'] ? 'up' : 'down';
            } else {
                $data['ranking_id'] = -1;
                $data['credit'] = -1;
                $data['status'] = 'invalid team';
            }

        }

        return $data;
    }
}
