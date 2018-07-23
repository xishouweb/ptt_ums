<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessUser extends Model
{
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
	
	public static function login($phone, $pwd)
	{
		if (!$phone || !$pwd) {
			return ['msg' => '手机和密码不能为空'];
		}

        $user = static::where('phone', $phone)->where('password', $pwd)->first();
		if ($user) {
			return $user;
		}
		return ['msg' => '账户不存在或密码错误'];
	}

    public static function register($phone, $pwd)
    {
        if (!$phone || !$pwd) {
            return ['msg' => '手机和密码不能为空'];
        }

        $user = static::where('phone', $phone)->where('password', $pwd)->first();
        if ($user) {
            return ['msg' => '该手机已被注册'];
        }
        return static::create([
            'phone' => $phone,
            'password' => $pwd,
            'update_key' => md5($phone . env('APP_KEY')),
        ]);
    }
}
