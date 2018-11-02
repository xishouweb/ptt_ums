<?php
/**
 * Created by PhpStorm.
 * User: Kana
 * Date: 2018/7/19
 * Time: 18:33
 */

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Captcha;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CaptchaController extends Controller
{
    protected $content = [];

    /**
     * Send verify code to mobile phone
     *
     * @param Request $request
     * @return mixed
     */
    public function send(Request $request)
    {
        $data['phone'] = (string)$request->input('phone');
        $data['country'] = (string)$request->input('country');

        $validator = Validator::make($data, [
            'phone' => 'required|string|size:11',
            'country' => 'required|string',
        ]);

        if ($validator->fails()) {
            $this->content['msg'] = '请输入正确的手机号';
            $this->content['status'] = 403;
            return response()->json($this->content);
        }

        if (User::where('phone', $data['phone'])->count()) {
            $this->content['msg'] = '该手机号已被注册';
            $this->content['status'] = 403;
            return response()->json($this->content);
        }

        $captcha = Captcha::send($data['phone'], $data['country']);
        if ($captcha) {
            $this->content['msg'] = '验证码已发送';
            $this->content['status'] = 200;
        } else {
            $this->content['msg'] = '请重试';
            $this->content['status'] = 403;
        }
        return response()->json($this->content);
    }
}