<?php

namespace App\Http\Controllers\MarkSix;

use App\Models\MarkSixBetHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
            'bet_amount' => $bet_amount * 1000000000000000000,
            'round'      => $round,
        ]);
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
        $numbers = array_unique(json_decode(json_encode($request->input('numbers'))));
        $special_number = $request->input('special_number');
        if (!$round || !$numbers || !$special_number || !is_array($numbers) || count(array_unique($numbers)) != 6 || $special_number < 1 || $special_number > 49 ) {
            return $this->_bad_json('无效参数');
        }
        $histories = MarkSixBetHistory::where('round', $round)->get();
        foreach ($histories as $history) {
            $status = self::checkWinningNumbers(json_decode($numbers), $special_number, json_decode($history->numbers));
            $history->status = $status;
            $history->save();
        }
        return $this->apiResponse();
    }

    public static function checkWinningNumbers($winning_numbers, $special_number, $user_numbers)
    {
        $count = count(array_intersect($winning_numbers, $user_numbers));
        $flag = in_array($special_number, $user_numbers);
        if ($count == 6) {
            //一等奖 选中6个“搅出号码”
            return MarkSixBetHistory::STATUS_FIRST_PRIZE;
        } else if ($count == 5 && $flag) {
            //二等奖 选中5个“搅出号码”加“特别号码”
            return MarkSixBetHistory::STATUS_SECOND_PRIZE;
        } else if ($count == 5) {
            //三等奖 选中5个“搅出号码”
            return MarkSixBetHistory::STATUS_THIRD_PRIZE;
        } else if ($count == 4 && $flag) {
            //四等奖 选中4个“搅出号码”加“特别号码”
            return MarkSixBetHistory::STATUS_FOURTH_PRIZE;
        } else if ($count == 4) {
            //五等奖 选中4个“搅出号码”
            return MarkSixBetHistory::STATUS_FIFTH_PRIZE;
        } else if ($count == 3 && $flag) {
            //六等奖 选中3个“搅出号码”加“特别号码”
            return MarkSixBetHistory::STATUS_SIXTH_PRIZE;
        } else if ($count == 3) {
            //七等奖 选中3个“搅出号码”
            return MarkSixBetHistory::STATUS_SEVENTH_PRIZE;
        }
        return MarkSixBetHistory::STATUS_LOSING_LOTTERY;
    }
}
