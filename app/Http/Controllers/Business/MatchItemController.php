<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MatchItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;

class MatchItemController extends Controller
{

	public function index()
	{
	    $user = Auth::user();
		$items = MatchItem::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->select('id', 'status', 'content', 'created_at')
            ->paginate(10);
		foreach ($items as $item) {
		    $item->name = property_exists(json_decode($item->content), 'name') ? json_decode($item->content)->name : '';
            $item->status = MatchItem::STATUS_TEXT[$item->status];
            unset($item->content);
        }
		return response()->json(['data' => $items]);
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
	    $user = Auth::user();
		try{
			if ($request->get('content')) {
				$data = [
				    'user_id' => $user->id,
					'content' => $request->get('content'),
				];
				MatchItem::create($data);
			}
			return response()->json(['msg' => 'success']);
		} catch(Exception $e) {
			Log::error('合约创建失败!', [$e->getMessage()]);
			return response()->json(['msg' => 'failed !']);
		}
	}
}
