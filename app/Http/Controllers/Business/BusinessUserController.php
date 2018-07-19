<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use App\Models\BusinessUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BusinessUserController extends Controller
{

    protected $content = [];

    public function login(Request $request)
    {
        $result = Auth::attempt(['email' => $request->input('email'), 'password' => $request->input('password')]);
        if ($result) {
            $user = Auth::user();
            $this->content['token'] = 'Bearer ' . $user->createToken('Api')->accessToken;
            $this->content['msg'] = '登录成功';
            $status = 200;
        } else {
            $this->content['msg'] = '账户不存在或密码错误';
            $status = 401;
        }
        return response()->json($this->content, $status);
    }

    public function passport()
    {
        return response()->json(['user' => Auth::user()]);
    }
}
