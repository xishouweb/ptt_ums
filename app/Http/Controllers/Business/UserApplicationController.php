<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserApplicationController extends Controller
{

	public function index(Request $request)
	{
        $data = UserApplication::orderBy('id', 'desc')->paginate(10);
		return response()->json(['data' => $data]);
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
        $user = Auth::user();
		if ($request->get('name')) {
			$data = [
				'name' => $request->get('name'),
				'user_id' => $user->id,
			];
			UserApplication::create($data);
            $data['status'] = 200;
            $data['msg'] = '创建成功';
		} else {
            $data['status'] = 401;
            $data['msg'] = '创建失败';
        }

		return response()->json($data);
	}
}
