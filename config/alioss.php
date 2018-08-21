<?php
/**
 * Created by PhpStorm.
 * User: erdangjia
 * Date: 2018/8/9
 * Time: 下午6:02
 */

return [
    'ossServer' => env('OSS_SERVER', null),                      // 外网
    'ossServerInternal' => env('OSS_SERVER_INTERNAL', null),      // 内网
    'accessKeyId' => env('OSS_KEY_ID', null),                     // key
    'accessKeySecret' => env('OSS_KEY_SECRET', null),             // secret
    'bucketName' => env('OSS_BUCKET_NAME', null),                  // bucket
    'city' => env('OSS_CITY', null),                  // city
    'networkType' => env('OSS_NETWORK_TYPE', null),                  // network
    'isInternal' => env('OSS_IS_INTERNAL', false),                  // is internal
    'ossURL' => env('OSS_URL', false),                  //  oss url
];