<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWalletTransaction extends BaseModel implements FormatInterface
{
    use SoftDeletes;
    
    const FAILD_STATUS = 0;

    protected $guarded = ['id'];

    const IN_TYPE = 1;
    const OUT_TYPE = 2;
    const AWARD_TYPE = 3;

    const OUT_STATUS_FAIL = 0;
    const OUT_STATUS_SUCCESS = 1;
    const OUT_STATUS_PADDING = 2;
    const OUT_STATUS_TRANSFER = 3;

    const PTT = 'ptt';

    public function format($source = [])
    {
        $data['id'] = $this->id;
        $data['symbol'] = $this->symbol;
        $data['type'] = $this->type;
        $data['amount'] = $this->amount;
        $data['status'] = $this->status;
        $data['created_at'] = $this->created_at;
        $data['block_confirm'] = $this->block_confirm;
        $data['rate'] = $this->rate;
        return $data;
    }
}
