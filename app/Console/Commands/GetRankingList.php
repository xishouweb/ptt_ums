<?php

namespace App\Console\Commands;

use App\Models\DataCache;
use App\Models\RentRecord;
use App\Models\TokenVote;
use App\User;
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
            ->whereIn('action', [RentRecord::ACTION_JOIN_CAMPAIGN, RentRecord::ACTION_JOIN_TEAM, RentRecord::ACTION_DEDUCTION])
            ->groupBy('team_id')
            ->select('team_id', \DB::raw("SUM(token_amount) as total"))
            ->orderBy('total', 'desc')
            ->get();

        foreach ($ranks as $rank) {
            $score = $rank->total * User::CREDIT_TOKEN_RATIO + TokenVote::totalVoteOf($rank->team_id) * User::CREDIT_VOTE_RATIO;
            DataCache::zincrOfCreditRankFor($rank->team_id, $score);
        }

    }
}
