<?php

namespace App\Models;

use App\Jobs\QueryBlockChain;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class MatchItem extends Model
{
	use SoftDeletes;

	protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();
        static::created(function ($model) {
            //插入队列去区块链获取信息
            Log::info('created');
            QueryBlockChain::dispatch($model)->delay(60);
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
		$data_t_id = ['id' => $item->id];
		$data['projectCount'] = rand(5, 10);
		$data['matchingDegree'] = $item->rant;
		$data['matchingPeople'] = $item->count;
		$data['date'] = (string)$item->created_at;
		$data = array_merge($data_t_id, $data);
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
