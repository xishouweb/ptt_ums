<?php
/**
 * Created by PhpStorm.
 * User: erdangjia
 * Date: 2018/8/9
 * Time: 下午6:02
 */

/**
 * ossServer            外网
 * ossServerInternal    内网
 * accessKeyId          key
 * accessKeySecret      secret
 * bucketName           bucket
 * city                 city
 * networkType          network
 * isInternal           is internal
 * ossURL               oss url
 */
return [
    'ossServer' => env('OSS_SERVER', 'http://oss-cn-hangzhou.aliyuncs.com'),
    'ossServerInternal' => env('OSS_SERVER_INTERNAL', 'http://oss-cn-hangzhou-internal.aliyuncs.com'),
    'accessKeyId' => env('OSS_KEY_ID', 'PNT702y47BBk78d6'),
    'accessKeySecret' => env('OSS_KEY_SECRET', 'Ta6sUmuR7byCnpPuyEaugzPO7zGguj'),
    'bucketName' => env('OSS_BUCKET_NAME', 'btkverifiedfiles'),
    'city' => env('OSS_CITY', '杭州'),
    'networkType' => env('OSS_NETWORK_TYPE', '经典网络'),
    'isInternal' => env('OSS_IS_INTERNAL', false),
    'ossURL' => env('OSS_URL', 'http://btkverifiedfiles.oss-cn-hangzhou.aliyuncs.com'),
];
