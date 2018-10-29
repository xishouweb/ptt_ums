<?php

namespace App\Http\Controllers;

use App\Models\Captcha;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CaptchaController extends Controller
{
    public function send(Request $request)
    {
        $data['phone'] = (string)$request->input('phone');
        $data['country'] = (string)$request->input('country');

        $validator = Validator::make($data, [
            'phone' => 'required|string|size:11',
            'country' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->_bad_json('请输入正确的手机号');
        }

        $captcha = Captcha::send($data['phone'], $data['country']);
        if (!$captcha) {
            return $this->_bad_json('发送失败,请重试');
        }

        return $this->_success_json([],'验证码已发送');
    }
}
