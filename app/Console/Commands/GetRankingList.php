<?php

namespace App\Console\Commands;

use App\Models\DataCache;
use App\Models\RentRecord;
use Illuminate\Console\Command;

class GetRankingList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Rank:getRankingList';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '超级广告主, 排行榜列表';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ranks = RentRecord::where('campaign_id', 1)
            ->where('token_type', 'ptt')
            ->whereIn('action', [RentRecord::ACTION_JOIN_CAMPAIGN, RentRecord::ACTION_JOIN_TEAM])
            ->groupBy('team_id')
            ->select('team_id', \DB::raw("SUM(token_amount) as total"))
            ->orderBy('total', 'desc')
            ->get();

        foreach ($ranks as $key => $rank) {
            $rank['ranking_id'] = $key + 1;
            DataCache::putRanking($rank);
        }

    }
}
