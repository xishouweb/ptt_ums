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
use Laravel\Passport\Events\AccessTokenCreated;

class RevokeOldTokens
{
    public function handle(AccessTokenCreated $event)
    {
        DB::table('oauth_access_tokens')
            ->where('id', '!=', $event->tokenId)
            ->where('user_id', $event->userId)
            ->where('client_id', $event->clientId)
            ->where('revoked', 0)
            ->delete();
    }
}