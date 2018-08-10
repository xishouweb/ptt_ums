<?php
/**
 * Created by PhpStorm.
 * User: erdangjia
 * Date: 2018/8/9
 * Time: 下午6:02
 */

return [
    'ossServer' => env('OSS_SERVER', null),                      // 外网
    'ossServerInternal' => env('OSS_SERVERINTERNAL', null),      // 内网
    'accessKeyId' => env('OSS_KEYID', null),                     // key
    'accessKeySecret' => env('OSS_KEYSECRET', null),             // secret
    'bucketName' => env('OSS_BUCKETNAME', null),                  // bucket
    'city' => env('OSS_CITY', null),                  // bucket
    'netWorkType' => env('OSS_NETWORK_TYPE', null),                  // bucket
];