<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Dashboard;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $data = Dashboard::where('user_id', $user->id)
            ->select('')
            ->orderBy('id', 'desc')
            ->limit(7)
            ->get();
        return response()->json($data);
    }
}
