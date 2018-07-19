<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessUser extends Model
{
	//
	use SoftDeletes;

	protected $guarded = ['id'];

	public function toArray()
	{
		$array = parent::toArray();
		$this->default_values($array);
		return $array;
	}

	public function default_values(&$array)
	{
		$array['nickname'] = '测试用户';
		$array['avatar'] = 'https://avatars2.githubusercontent.com/u/26914316?s=40&v=4';
		$array['token'] = '123451234512345';
	}

	public static function login($address)
	{
		if (!$address) {
			return ['msg' => 'address not null'];
		}

		if ($user = static::whereAddress($address)->first()) {
			return $user;
		} else {
			$user = static::create(['address' => $address]);
			return static::find($user->id);
		}
	}

	public static function scanLogin($phone)
	{
		if (!$phone) {
			return ['code' => 404, 'data' => null, 'msg' => 'phone not null'];
		}

		if ($user = static::whereAddress($phone)->first()) {
			if ($user->password === request()->get('password')) {
				return ['code' => 200, 'data' => $user, 'msg' => 'success'];
			}
		}

		return ['code' => 404, 'data' => null, 'msg' => 'phone not null'];
	}
}
