<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MatchItem extends Model
{
	use SoftDeletes;	

	protected $guarded = ['id'];
	

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
		$data['date'] = date('Y-m-d H:i:s');
		$data = array_merge($data_t_id, $data);
		return $data;	
	}
}
