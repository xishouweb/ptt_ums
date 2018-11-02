<?php
/**
 * Created by PhpStorm.
 * User: Kana
 * Date: 2018/11/2
 * Time: 17:59
 */
namespace App\Listeners;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Events\AccessTokenCreated;

class ChangeTokenExpiresTime
{
    public function handle(AccessTokenCreated $event)
    {
        DB::table('oauth_access_tokens')
            ->where('id', $event->tokenId)
            ->where('user_id', $event->userId)
            ->where('client_id', $event->clientId)
            ->update(['expires_at' => date('Y-m-d H:i:s', strtotime('+7 day'))]);
    }
}