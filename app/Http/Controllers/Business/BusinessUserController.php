<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusinessUser;

class BusinessUserController extends Controller
{

	public function login(Request $request)
	{
		return response()->json(BusinessUser::login($request->get('address')));			
	}

}
