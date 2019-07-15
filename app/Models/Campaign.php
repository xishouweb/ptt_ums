<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends BaseModel implements FormatInterface
{
    use SoftDeletes;

    const STATUS_NORMAL = 1;
    const PRICE_QUERY = 2;

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
        $record = RentRecord::where('campaign_id', $this->id)->select(\DB::raw('count(distinct(user_id)) as aaa'))->first();

        return $record ? $record->aaa : 0;
    }

    private function totalPtt()
    {
        return RentRecord::where('campaign_id', $this->id)
            ->sum('token_amount') ?? 0;
    }
}
