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
        $xuRobotId = $data->vcRobotSerialNo;

        try {
            \DB::beginTransaction();

            PriceQueryLog::create([
                'campaign_id' => $this->campaign_id,
                'xu_host_id' => $xuHostId,
                'xu_group_id' => $xuGroupId,
                'xu_robot_id' => $xuRobotId,
                'symbol' => $symbol,
            ]);

            $record = PriceQueryStatistic::whereCampaignId($this->campaign_id)
                ->whereXuHostId($xuHostId)
                ->whereXuGroupId($xuGroupId)
                ->whereXuRobotId($xuRobotId)
                ->whereSymbol($symbol)
                ->first();
            if ($record) {
                $record->query_count += 1;
                $record->save();
            } else {
                PriceQueryStatistic::create([
                    'campaign_id' => $this->campaign_id,
                    'xu_host_id' => $xuHostId,
                    'xu_group_id' => $xuGroupId,
                    'xu_robot_id' => $xuRobotId,
                    'symbol' => $symbol,
                    'query_count' => 1,
                ]);
            }

            $user = UserXuHost::whereXuHostId($xuHostId)->whereXuNickname($xuNickname)->first();
            if (!$user) {
                UserXuHost::create([
                    'xu_host_id' => $xuHostId,
                    'xu_nickname' => $xuNickname,
                ]);
            }

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollback();
            \Log::error($e->getMessage());
        }

    }
}
