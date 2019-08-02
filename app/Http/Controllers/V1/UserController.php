<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function redirectXu()
    {
        header("Location: http://www.baidu.com");
    }
}
