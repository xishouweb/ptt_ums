<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\PriceQueryLog;
use App\Models\UserXuHost;
use App\Models\PriceQueryStatistic;
use App\Models\WechatUsers;
use App\User;

class XuCallbackRecord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $campaign_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $campaign_id)
    {
        $this->data = $data;
        $this->campaign_id = $campaign_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;
        $symbol = strtolower($data->vcKeyword);
        $xuHostId = $data->vcWxUserSerialNo;
        $xuNickname = $data->vcWxUserNickName;
        $xuGroupId = $data->vcChatRoomSerialNo;
        $xuGroupName = $data->vcChatRoomName;
        $xuRobotId = $data->vcRobotSerialNo;

        $user_id = null;
        $xuUser = UserXuHost::whereXuHostId($xuHostId)->first();
        if ($xuUser && $xuUser->unionid) {
            $wechatUser = WechatUsers::whereOpenid($xuUser->unionid)->first();
            if ($wechatUser) {
                $user = User::whereUnionid($wechatUser->unionid)->first();
                if ($user) {
                    $user_id = $user->id;
                }
            }
        }

        try {
            \DB::beginTransaction();


            PriceQueryLog::create([
                'campaign_id' => $this->campaign_id,
                'xu_host_id' => $xuHostId,
                'xu_group_id' => $xuGroupId,
                'xu_group_name' => $xuGroupName,
                'xu_robot_id' => $xuRobotId,
                'symbol' => $symbol,
                'user_id' => $user_id,
            ]);

            $record = PriceQueryStatistic::whereCampaignId($this->campaign_id)
                ->whereXuHostId($xuHostId)
                ->whereXuGroupId($xuGroupId)
                ->whereXuRobotId($xuRobotId)
                ->whereSymbol($symbol)
                ->first();
            if ($record) {
                $record->query_count += 1;
                if (!$record->xu_group_name) {
                    $record->xu_group_name = $xuGroupName;
                }
                $record->save();
            } else {
                PriceQueryStatistic::create([
                    'campaign_id' => $this->campaign_id,
                    'xu_host_id' => $xuHostId,
                    'xu_group_id' => $xuGroupId,
                    'xu_group_name' => $xuGroupName,
                    'xu_robot_id' => $xuRobotId,
                    'symbol' => $symbol,
                    'query_count' => 1,
                    'user_id' => $user_id,
                ]);
            }

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollback();
            \Log::error($e->getMessage());
        }

    }
}
