<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MatchItem;

class MatchItemController extends Controller
{

	public function index(Request $request)
	{
		$items = MatchItem::orderBy('id', 'desc')->paginate(10);
		return response()->json(['all' => MatchItem::format_list($items)]);
	}

	public function show($id)
	{
		$data = [];
		if ($item = MatchItem::where('id', $id)->first()) {
			$data = MatchItem::format($item);			
		}
		return response()->json($data);
	}

	public function store(Request $request)
	{
		if ($request->get('content')) {
			$data = [
				'content' => $request->get('content'),	
				'rant' => rand(0, 100) / 100,
				'count' => rand(10000, 99999)
			];
			MatchItem::create($data);
		}
		return response()->json(['msg' => 'success']);
	}
}
