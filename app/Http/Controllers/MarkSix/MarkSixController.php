<?php

namespace App\Http\Controllers\MarkSix;

use App\Jobs\MarkSixCheckTransactionStatus;
use App\Models\MarkSixBetHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class MarkSixController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $address = $request->input('address', '');
        $histories = MarkSixBetHistory::where('address', $address)
            ->orderBy('id', 'desc')
            ->paginate(10);
        return $this->response($histories);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $address    = $request->input('address');
        $tx_hash    = $request->input('tx_hash');
        $numbers    = $request->input('numbers');
        $bet_amount = $request->input('bet_amount');
        $round      = $request->input('round');
        if (!$address || !$tx_hash || !$numbers || !$bet_amount || !$round) {
            return $this->_bad_json('参数错误', 400);
        }
        $history = MarkSixBetHistory::create([
            'address'    => $address,
            'tx_hash'    => $tx_hash,
            'numbers'    => $numbers,
            'bet_amount' => $bet_amount,
            'round'      => $round,
        ]);
        dispatch(new MarkSixCheckTransactionStatus($history->id, $tx_hash))->delay(now()->addMinutes(30))->onQueue('check');
        return $this->apiResponse($history);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function draw(Request $request)
    {
        $round = $request->input('round');
        $numbers = array_unique(json_decode($request->input('numbers'), true));
        $special_number = $request->input('special_number');
        if (!$round || !$numbers || !$special_number || !is_array($numbers) || count(array_unique($numbers)) != 6 || $special_number < 1 || $special_number > 49 ) {
            return $this->_bad_json('无效参数');
        }
        $histories = MarkSixBetHistory::where('round', $round)->where('status', MarkSixBetHistory::STATUS_SUCCESS_BETTING)->get();
        foreach ($histories as $history) {
            $status = self::checkWinningNumbers($numbers, $special_number, json_decode($history->numbers));
            $history->status = $status;
            $history->save();
        }
        return $this->apiResponse();
    }

    public static function checkWinningNumbers($winning_numbers, $special_number, $user_numbers)
    {
        $count = count(array_intersect($winning_numbers, $user_numbers));
        $flag = in_array($special_number, $user_numbers);
        if ($count < 3) {
            return MarkSixBetHistory::STATUS_LOSING_LOTTERY;
        } else if ($count == 3 && $flag) {
            //六等奖 选中3个“搅出号码”加“特别号码”
            return MarkSixBetHistory::STATUS_SIXTH_PRIZE;
        } else if ($count == 3) {
            //七等奖 选中3个“搅出号码”
            return MarkSixBetHistory::STATUS_SEVENTH_PRIZE;
        } else if ($count == 4 && $flag) {
            //四等奖 选中4个“搅出号码”加“特别号码”
            return MarkSixBetHistory::STATUS_FOURTH_PRIZE;
        } else if ($count == 4) {
            //五等奖 选中4个“搅出号码”
            return MarkSixBetHistory::STATUS_FIFTH_PRIZE;
        } else if ($count == 5 && $flag) {
            //二等奖 选中5个“搅出号码”加“特别号码”
            return MarkSixBetHistory::STATUS_SECOND_PRIZE;
        } else if ($count == 5) {
            //三等奖 选中5个“搅出号码”
            return MarkSixBetHistory::STATUS_THIRD_PRIZE;
        } else if ($count == 6) {
            //一等奖 选中6个“搅出号码”
            return MarkSixBetHistory::STATUS_FIRST_PRIZE;
        }
    }

    public function setAward(Request $request)
    {
        $round         = $request->input('round');
        $first_prize   = $request->input('first_prize');
        $second_prize  = $request->input('second_prize');
        $third_prize   = $request->input('third_prize');
        $fourth_prize  = $request->input('fourth_prize');
        $fifth_prize   = $request->input('fifth_prize');
        $sixth_prize   = $request->input('sixth_prize');
        $seventh_prize = $request->input('seventh_prize');
        if (!$round || !$first_prize || !$second_prize || !$third_prize || !$fourth_prize || !$fifth_prize || !$sixth_prize || !$seventh_prize) {
            return $this->_bad_json('无效参数');
        }
        $histories = MarkSixBetHistory::where('round', $round)->get();
        foreach ($histories as $history) {
            $number = $history->bet_amount * 100;
            if ($history->status == MarkSixBetHistory::STATUS_LOSING_LOTTERY) {
                $history->award_amount = 0;
            } else if ($history->status == MarkSixBetHistory::STATUS_SEVENTH_PRIZE) {
                $history->award_amount = $seventh_prize * $number;
            } else if ($history->status == MarkSixBetHistory::STATUS_SIXTH_PRIZE) {
                $history->award_amount = $sixth_prize * $number;
            } else if ($history->status == MarkSixBetHistory::STATUS_FIFTH_PRIZE) {
                $history->award_amount = $fifth_prize * $number;
            } else if ($history->status == MarkSixBetHistory::STATUS_FOURTH_PRIZE) {
                $history->award_amount = $fourth_prize * $number;
            } else if ($history->status == MarkSixBetHistory::STATUS_THIRD_PRIZE) {
                $history->award_amount = $third_prize * $number;
            } else if ($history->status == MarkSixBetHistory::STATUS_SECOND_PRIZE) {
                $history->award_amount = $second_prize * $number;
            } else if ($history->status == MarkSixBetHistory::STATUS_FIRST_PRIZE) {
                $history->award_amount = $first_prize * $number;
            }
            $history->save();
        }
        return $this->apiResponse();
    }

    public function rankingList(Request $request)
    {
        $round = $request->input('round');
        $histories = MarkSixBetHistory::where('round', $round)
            ->whereIn('status', [
                MarkSixBetHistory::STATUS_FIRST_PRIZE,
                MarkSixBetHistory::STATUS_SECOND_PRIZE,
                MarkSixBetHistory::STATUS_THIRD_PRIZE,
                MarkSixBetHistory::STATUS_FOURTH_PRIZE,
                MarkSixBetHistory::STATUS_FIFTH_PRIZE,
                MarkSixBetHistory::STATUS_SIXTH_PRIZE,
                MarkSixBetHistory::STATUS_SEVENTH_PRIZE,
            ])
            ->orderBy('status')
            ->orderBy('award_amount', 'desc')
            ->paginate(10);
        return $this->response($histories);
    }

    public function winningInfo(Request $request)
    {
        $round = $request->input('round');
        $address = $request->input('address');
        if (!$round || !$first_prize || !$second_prize || !$third_prize || !$fourth_prize || !$fifth_prize || !$sixth_prize || !$seventh_prize) {
            return $this->_bad_json('无效参数');
        }
        $history = MarkSixBetHistory::where('round', $round)
            ->where('address', $address)
            ->whereIn('status', [
                MarkSixBetHistory::STATUS_FIRST_PRIZE,
                MarkSixBetHistory::STATUS_SECOND_PRIZE,
                MarkSixBetHistory::STATUS_THIRD_PRIZE,
                MarkSixBetHistory::STATUS_FOURTH_PRIZE,
                MarkSixBetHistory::STATUS_FIFTH_PRIZE,
                MarkSixBetHistory::STATUS_SIXTH_PRIZE,
                MarkSixBetHistory::STATUS_SEVENTH_PRIZE,
            ])
            ->select('award_amount', 'bet_amount', 'numbers', 'status')
            ->orderBy('status')
            ->orderBy('award_amount', 'desc')
            ->first();
        return $this->response($history);
    }
}
