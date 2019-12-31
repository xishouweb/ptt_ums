<?php
namespace App\Services;
 
use GuzzleHttp\Client;

class PttMasterAcount {

    public static function sendTransaction($to, $value, $data = ''){
        $url = config('app.ptt_ums_node_host') . "/eth/send_tx";
       
        $client = new Client();
        $res = $client->request('post', $url, [
            'form_params' => [
                'from' => config('app.ptt_master_address'),
                'to' => $to,
                'value' => $value,
                'data' => $data,
                'gasPrice' => static::getGasPrice(),
                'gas' => 23000,
                'keystoreJson' => config('app.ptt_master_address_keystore'),
                'password' => config('app.ptt_master_address_password'),
            ]
        ]);

        $resData  = json_decode((string) $res->getBody(), true);

        return $resData;
    }


    public static function getGasPrice(){
        $url = config('app.ptt_ums_node_host') . "/eth/gas";
        
        $client = new Client();
        $res = $client->request('get', $url);
        $resData  = json_decode((string) $res->getBody());

        return $resData;
    }

    public static function getBalance($address){
        $url = config('app.ptt_ums_node_host') . "/eth/balance?account=$address";
        
        $client = new Client();
        $res = $client->request('get', $url);
        $resData  = json_decode((string) $res->getBody());

        return $resData;
    }

}