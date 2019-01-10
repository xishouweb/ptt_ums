<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Dashboard;
use App\Models\MatchItem;
use App\Models\ProtonNew;
use App\Models\UserApplication;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    public function chartData()
    {
        $user = Auth::user();
        $items = Dashboard::where('user_id', $user->id)
            ->where('type', Dashboard::UPLOAD_DATA)
            ->select('created_at', 'value')
            ->orderBy('id')
            ->limit(14)
            ->get();
        foreach ($items as $item) {
            $item->day = date('m-d', strtotime((string)$item->created_at));
            $item->count = (int)$item->value;
            unset($item->value);
            unset($item->created_at);
        }
        return response()->json($items);
    }

    public function summary()
    {
        $user = Auth::user();
        $today_upload_count = Dashboard::where('user_id', $user->id)
            ->where('type', Dashboard::UPLOAD_DATA)
            ->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])
            ->select('value')
            ->sum('value');
        $upload_count = Dashboard::where('user_id', $user->id)
            ->where('type', Dashboard::UPLOAD_DATA)
            ->select('value')
            ->sum('value');
        $contract_count = MatchItem::where('user_id', $user->id)
            ->select('id')
            ->count(['id']);
        $source_count = UserApplication::where('user_id', $user->id)
            ->select('id')
            ->count(['id']);
        $data = [
            [
                'text' => '合约数',
                'number' => $contract_count,
                'imgUrl' => 'https://img.alicdn.com/tfs/TB1tlVMcgmTBuNjy1XbXXaMrVXa-140-140.png',
                'desc' => '相关说明',
            ],
            [
                'text' => '数据源',
                'number' => $source_count,
                'imgUrl' => 'https://img.alicdn.com/tfs/TB1Py4_ceuSBuNjy1XcXXcYjFXa-142-140.png',
                'desc' => '相关说明',
            ],
            [
                'text' => '数据量',
                'number' => $upload_count,
                'imgUrl' =>'https://img.alicdn.com/tfs/TB1Ni4_ceuSBuNjy1XcXXcYjFXa-142-140.png',
                'desc' => '相关说明',
            ],
            [
                'text' => '今日数据量',
                'number' => $today_upload_count,
                'imgUrl' => 'https://img.alicdn.com/tfs/TB1iFKccamWBuNjy1XaXXXCbXXa-140-140.png',
                'desc' => '相关说明',
            ],
        ];
        return response()->json($data);
    }

    public function news()
    {
        if (!$type = request()->get('type')) {
            return $this->apiResponse([], 'Regional illegality', 1);
        }
        $news = ProtonNew::where('status', ProtonNew::STASUS_NOMAL)
            ->whereType($type)
            ->orderBy('is_top', 'desc')
            ->orderBy('release_date', 'desc')
            ->paginate(6);
        return response()->json(['data' => $news]);
    }
}
