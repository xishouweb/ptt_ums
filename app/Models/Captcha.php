<?php

namespace App\Models;

use App\Services\SMSSender;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use \Yunpian\Sdk\YunpianClient;

class Captcha extends Model
{

	public static function voice_sms($mobile, $country = '86')
	{
	    $code = rand(100000, 999999);

        $model             = new Captcha();
        $model->mobile     = $mobile;
        $model->country    = $country;
        $model->code       = $code;
        $model->status     = 0;
        $model->expired_at = date('Y-m-d H:i:s', time() + config('app.captcha_live_time'));
        $model->save();
		Log::info('[voice_captcha]: phone is ' . $mobile . ', code is ' . $code);
		self::voice($mobile, $code);
		return $model;
	}

	public static function voice($mobile, $code)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://voice-api.luosimao.com/v1/verify.json");
		
		curl_setopt($ch, CURLOPT_HTTP_VERSION  , CURL_HTTP_VERSION_1_0 );
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		
		curl_setopt($ch, CURLOPT_HTTPAUTH , CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD  , 'api:key-31441c425d8798eb9ca3ca923f509008');
		
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $mobile,'code' => $code));
		
		curl_exec( $ch );
		curl_close( $ch );
		Log::info(json_encode($ch));
	}

    /**
     * Create a new captcha
     *
     * @param $mobile
     * @return Captcha|bool
     */
    public static function send($mobile, $country = '86')
    {
		if (starts_with($mobile, '170') || starts_with($mobile, '171')) {
			return self::voice_sms($mobile, $country);	
		}
		
        $code = rand(100000, 999999);

        $model             = new Captcha();
        $model->mobile     = $mobile;
        $model->country    = $country;
        $model->code       = $code;
        $model->status     = 0;
        $model->expired_at = date('Y-m-d H:i:s', time() + 180);
        $model->save();
		Log::info('[captcha]: phone is ' . $mobile . ', code is ' . $code);
        //$model->dispatch((new SendSMS($mobile, $code, $country))->onQueue('sms'));
		self::sms($mobile, $code, $country);
        return $model;
    }

	public static function sms($mobile, $code, $country)
	{
	    if ($country == '86') {
            $message = '【Proton质子链】您的验证码是'.$code.'，请勿告诉他人。';
            // luosimao
            SMSSender::send($mobile, $message);
        } else {
            $phone = '+' . $country . $mobile;
            $message = '【Proton Chain】your verification code is: '.$code;
            // yunpian
            $clnt = YunpianClient::create(config('app.yunpian_apikey'));
            $param = [YunpianClient::MOBILE => $phone, YunpianClient::TEXT => $message];
            $r = $clnt->sms()->single_send($param);
            Log::info($r);
//            if(!$r->isSucc()){
//                Log::error($r->getThrowable());
//            }
        }

	}

    /**
     * Verify sms code
     *
     * @param $mobile
     * @param $code
     * @return bool
     */
    public static function valid($mobile, $code)
    {
        $model = self::where('mobile', $mobile)->where('code', $code)->orderBy('created_at', 'desc')->validity()->first();

        if ($model) {
            $model->status = 1;
            $model->save();
            return true;
        }
        return false;
    }

    public function scopeValidity($query)
    {
        return $query->where('status', 0)->where("expired_at", ">", date('Y-m-d H:i:s'));
    }

}
