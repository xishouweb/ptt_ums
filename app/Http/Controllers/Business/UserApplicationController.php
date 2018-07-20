<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserApplication;
use Illuminate\Support\Facades\Log;

class UserApplicationController extends Controller
{

	public function index(Request $request)
	{
		$items = UserApplication::orderBy('id', 'desc')->paginate(10);
		return response()->json(['items' => $items]);
	}

	public function show($id)
	{
		$data = [];
		if ($item = UserApplication::where('id', $id)->first()) {
			return $item;
		}
		return response()->json($data);
	}

	public function store(Request $request)
	{
	    //todo auth

		if ($request->get('name')) {
			$data = [
				'name' => $request->get('content'),
				'user_id' => $request->get('user_id'),
			];
			UserApplication::create($data);
		}
		return response()->json(['msg' => 'success']);
	}
}
