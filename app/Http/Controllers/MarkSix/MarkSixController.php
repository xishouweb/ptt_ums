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
}
