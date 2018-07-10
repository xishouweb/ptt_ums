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
            QueryBlockChain::dispatch($model);
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

	public static function transferFormat($data)
    {

    }
}
