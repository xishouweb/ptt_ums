<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserApplication;
use App\Models\DatRecord;

class DataRecordController extends Controller
{

	public function index(Request $request)
	{
		$user = Auth::user();
		$items = DataRecord::where('user_id', $user->id)->orderBy('id', 'desc')->paginate(20);
		
		$results = [];	

		foreach ($items as $item) {
			$d = [];	
			$d['id'] = $item->id;
			$d['user_application'] = UserApplication::find($item->user_application_id)->name;
			$d['txhash'] = $item->txhash;
			$d['uid'] = $item->uid;
			$d['created_at'] = $item->created_at;
			$results[] = $d;
		}
		
		return response()->json(['items' => $results]);
	}
}
