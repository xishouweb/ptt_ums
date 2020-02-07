<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserActionHistory extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    const TYPE_IN = 1;
    const TYPE_OUT = 2;
    const TYPE_JOIN = 3;
    const TYPE_EXIT = 4;

    public static function record($user_id, $type = null, $transaction_id = null, $withdrawal_id = null, $remark = null, $symbol = 'ptt')
    {
        $data = [
            'user_id'        => $user_id,
            'type'           => $type,
            'transaction_id' => $transaction_id,
            'saving_id'      => null,
            'withdrawal_id'  => $withdrawal_id,
            'balance'        => 0,
            'remark'         => $remark,
        ];
        // 查询持仓
        $saving = Saving::where('type', Saving::TYPE_SAVING)
            ->where('status', Saving::SAVING_ACTIVATED_STATUS)
            ->where('started_at', '<=', date('Y-m-d H:i:s'))
            ->where('ended_at', '>=', date('Y-m-d H:i:s'))
            ->first();
        if ($saving) {
            $join_record = SavingParticipateRecord::where('user_id', $user_id)
                ->where('saving_id', $saving->id)
                ->where('status', SavingParticipateRecord::STATUS_JOIN)
                ->count();
            if ($join_record) {
                $data['saving_id'] = $saving->id;
            }
        }
        // 查询余额
        $balance_model = UserWalletBalance::where('user_id', $user_id)->where('symbol', $symbol)->first();
        if ($balance_model) {
            $data['balance'] = $balance_model->total_balance;
        }
        return static::create($data);
    }

    public function savings()
    {
        return $this->belongsTo(Saving::class, 'saving_id', 'id');
    }

    public function savingParticipateRecord()
    {   
        if ($this->saving_id) {
            return SavingParticipateRecord::whereUserId($this->user_id)->whereSavingId($this->saving_id)->first();
        }
        
        return [];
    }

    public function userWalletTransaction()
    {
        // if ($this->transaction_id) {
        //     return userWalletTransaction::find($this->transaction_id);
        // }
        
        // return [];
        return $this->hasOne(UserWalletTransaction::class, 'id', 'transaction_id');
    }
}
