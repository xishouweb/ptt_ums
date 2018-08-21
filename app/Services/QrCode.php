<?php
/**
 * Created by PhpStorm.
 * User: erdangjia
 * Date: 2018/8/13
 * Time: 下午2:55
 */

namespace App\Services;

use QrCode as Vender_QrCode;
use App\Models\Photo;

class QrCode {

    public static function getQrCodeUrl($content, $size, $user_id){
        $fileName = 'img/qrcodes/address_' . $user_id . '.png';
        $sysPath = getcwd();
        $realpath = '/img/qrcodes/';
        if (!file_exists($sysPath . $realpath)) {
            mkdir($sysPath . $realpath, 0777, true);
        }
        Vender_QrCode::format('png')->size($size)->merge('/public/img/logos/PTT-01.png', .2)->margin(2)->generate($content, $fileName);

        $url = Photo::simpleUpload($fileName, 'ptt/qrcode', $user_id);

        @unlink($fileName);

        return $url;
    }

}