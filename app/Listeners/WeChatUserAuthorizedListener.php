<?php

namespace App\Listeners;

use App\Models\WechatOpenid;
use App\Models\WechatUsers;
use App\Models\UserXuHost;
use Overtrue\LaravelWeChat\Events\WeChatUserAuthorized;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class WeChatUserAuthorizedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  WeChatUserAuthorized  $event
     * @return void
     */
    public function handle(WeChatUserAuthorized $event)
    {
        \Log::info('event => ', [$event] );


        $user = $event->user->original;
        if (isset($user['scope']) && $user['scope'] == 'snsapi_base') {
            UserXuHost::firstOrCreate([
                'union_id' => $user['openid'],
            ]);
        } else {
            $this->__recordUserInfo($user);
        }

    }

    private function __recordUserInfo($user)
    {
         WechatOpenid::firstOrCreate([
                'openid' => $user['openid'],
                'unionid' => $user['unionid'],
            ]);

            if (isset($user['unionid'])) {
                WechatUsers::updateOrCreate(
                    [
                        'openid' => $user['openid'],
                        'unionid' => $user['unionid']
                    ],
                    [
                        'nickname' => $user['nickname'],
                        'headimgurl' => $user['headimgurl'],
                        'sex' => $user['sex'],
                        'city' => $user['city'],
                        'country' => $user['country'],
                        'province' => $user['province'],
                        'language' => $user['language'],
                    ]
                );
            } else {
                WechatUsers::create(
                    [
                        'openid' => $user['openid'],
                        'nickname' => $user['nickname'],
                        'headimgurl' => $user['headimgurl'],
                        'sex' => $user['sex'],
                        'city' => $user['city'],
                        'country' => $user['country'],
                        'province' => $user['province'],
                        'language' => $user['language'],
                    ]
                );
            }
    }
}
