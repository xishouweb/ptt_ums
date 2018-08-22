<?php

namespace App\Http\Controllers\Campaign;

use App\Models\RentRecord;
use App\Models\TokenTransaction;
use App\Models\UserToken;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TokenTxController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        try{
            DB::beginTransaction();
            $data = $request->only(['user_id', 'blockchain_tx_hash', 'token_amount', 'token_type', 'campaign_id']);

            if (!isset($data['user_id']) || !$user = User::find($data['user_id'])) {
                throw new \Exception('未找到该用户');
            }
            $token = UserToken::where('user_id', $data['user_id'])->where('token_type', $data['toke_type'])->first();

            if (!$token) {
                $token = UserToken::create([
                    'user_id' => $data['user_id'],
                    'token_amount' => $data['token_amount'],
                    'token_type' => $data['token_type'],
                ]);

                $data['original_amount'] = 0;
                $data['after_amount'] = $token->token_amount;
            } else {
                $data['original_amount'] = $token->token_amount;
                $data['after_amount'] = $token->token_amount + $data['token_amount'];
            }


            $data['action'] = TokenTransaction::ACTION_TOP_UP;
            TokenTransaction::create($data);

            RentRecord::record($user, RentRecord::ACTION_SELF_IN . $user->id, $data['token_amount'], $data['token_type'], $data['campaign_id']);

            DB::commit();

            return $this->apiResponse();

        } catch (\Exception $e) {
            DB::rollback();
            
            return $this->apiResponse([], $e->getMessage(), 1);
        }
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
