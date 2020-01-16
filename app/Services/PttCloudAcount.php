<?php
namespace App\Services;
 
use GuzzleHttp\Client;

class PttCloudAcount {

    public static function sendTransaction($to, $value, $symbolName = "eth", $data = ''){
        $url = config('app.ptt_ums_node_host');
       
        $client = new Client();

        if ($symbolName == 'eth') {
            $url .=  "/eth/send_eth";
            $res = $client->request('post', $url, [
                'form_params' => [
                    'from' => config('app.ptt_master_address'),
                    'to' => $to,
                    'value' => $value,
                    'data' => $data,
                    'gasPrice' => self::getGasPrice(),
                    'gas' => 23000,
                    'keystoreJson' => config('app.ptt_master_address_keystore'),
                    'password' => config('app.ptt_master_address_password'),
                ]
            ]);
        } elseif ($symbolName == 'ptt') {
            $url .=  "/eth/send_ptt";
            $res = $client->request('post', $url, [
                'form_params' => [
                    'from' => $data['from'],
                    'to' => $to,
                    'value' => $value,
                    'gasPrice' => self::getGasPrice(),
                    'gas' => 60000,
                    'keystoreJson' => $data['keystore'],
                    'password' => $data['password'],
                ]
            ]);
        }

        

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

    public static function getBalance($address, $symbolName = ''){
        $url = config('app.ptt_ums_node_host') . "/eth/balance?account=$address";
        
        if($symbolName) {
            $url = $url .'&symbol=' . strtolower($symbolName); 
        }
        
        $client = new Client();
        $res = $client->request('get', $url);
        $resData  = json_decode((string) $res->getBody());

        return $resData;
    }

}