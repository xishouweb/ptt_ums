<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends BaseModel implements FormatInterface
{
    use SoftDeletes;

    public function format($source = [])
    {
        $data = $this;
        $data['each_income'] = 1;
        $data['each_income_unit'] = 'CNY';
        $data['people_amount'] = $this->totalNumberOfPeople();
        $data['ptt_amount'] = $this->totalPtt();
        return $data;
    }

    private function totalNumberOfPeople()
    {
        return RentRecord::where('campaign_id', $this->id)->select('distinct(user_id)')->count() ?? 0;
    }

    private function totalPtt()
    {
        return RentRecord::where('campaign_id', $this->id)
            ->sum('token_amount') ?? 0;
    }
}
