<?php

namespace App\Models;

use App\Jobs\QueryBlockChain;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class MatchItem extends Model
{
	use SoftDeletes;

	const QUERYING = 0;
	const COMPLETED = 1;
	const STATUS_TEXT = [
	    self::QUERYING => '正在查询',
        self::COMPLETED => '成功',
    ];

	protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();
        static::created(function ($model) {
            //插入队列去区块链获取信息
            Log::info('created');
            if ($user = auth()->user()) {
                QueryBlockChain::dispatch($model, $user->id);
            }
        });
    }

	public static function format_list($data)
	{
		$result = [];
		foreach($data as $d) {
			$result[] = $d->format($d);
		}
		return $result;
	}

	public static function format($item)
	{
		$data = json_decode($item->content, true);
		$data['id'] = $item->id;
        $data['status'] = self::STATUS_TEXT[$item->status];
		$data['projectCount'] = UserApplication::count() - 1;
		$data['matchingDegree'] = $item->rant;
		$data['matchingPeople'] = $item->count;
		$data['date'] = (string)$item->created_at;
		return $data;
	}

	public static function transferFormat($item)
    {
        $content = json_decode($item);
        $summary = [];
        $conditions = [];
        $details = [];
        foreach ($content->conditions as $data) {
            $summary[] = $data->condition;
            $conditions[$data->condition][] = $data->detail;
            $details[] = $data->detail;
        }
        $result = [
            'summary' => array_unique($summary),
            'conditions' => $conditions,
            'details' => $details
        ];
        return $result;
    }
}
