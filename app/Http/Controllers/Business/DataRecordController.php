<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\DataRecord;
use Illuminate\Http\Request;
use App\Models\UserApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\DatRecord;

class DataRecordController extends Controller
{

	public function index(Request $request)
	{
		$user = Auth::user();
		$items = DataRecord::join('user_applications', 'data_records.user_application_id', '=', 'user_applications.id')
            ->where('data_records.user_id', $user->id)
            ->orderBy('data_records.id', 'desc')
            ->paginate(20);
		return response()->json(['data' => $items]);
	}
}
