<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    /**
     * Standardize response
     *
     * @param string|array $data
     * @param string       $message
     * @param int          $code
     * @return mixed
     */
    public function apiResponse($data = [], $message = '操作成功', $code = 0)
    {
        return response()->json(['code' => $code, 'message' => $message, 'data' => $data]);
    }



}
