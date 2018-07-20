<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SMSSender
{
    public static function send($mobile, $message)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://sms-api.luosimao.com/v1/send.json");

        curl_setopt($ch, CURLOPT_HTTP_VERSION  , CURL_HTTP_VERSION_1_0 );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPAUTH , CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD  , 'api:key-dc7be5fc443d83855d3f4558dcfadb03');

        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $mobile,'message' => $message. '【Proton质子链】'));
        $res = curl_exec( $ch );
        curl_close( $ch );
        $res = json_decode($res, true);
        if( $res['error'] ){
            Log::error($res);
            return false;
        }
        return true;
    }
}
