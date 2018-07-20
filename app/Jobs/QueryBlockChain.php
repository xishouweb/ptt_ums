<?php

namespace App\Jobs;

use App\Models\DataRecord;
use App\Models\MatchItem;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryBlockChain implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $match_item;

    const BLOCK_CHAIN_URL = 'http://p1.analytab.net:8888/gethash/';
    const IPFS_URL = 'http://ipfs.analytab.net/ipfs/';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MatchItem $match_item)
    {
        $this->match_item = $match_item;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('handle');
        //获取创建合约内容
        $content = MatchItem::transferFormat($this->match_item->content);

        //根据内容查出匹配数据
        $bc_ids = self::getQualifiedHash($content['summary']);
        $count = count($bc_ids);                                //总匹配数据的个数
        $qualified_count = 0;                                   //合格数据的个数

        //根据匹配的bc_id，从链上查询IPFS HASH
        $i_hashs = self::getIHash($bc_ids);


        //根据IPFS HASH去IPFS上查询原始数据
        $json_list = self::getFullJson($i_hashs);

        //根据原始数据，匹配出合格的数据
        foreach ($json_list as $json) {
            if (self::checkJson($content['details'], $json)) {
                $qualified_count += 1;
            }
        }

        //将比对结果存储
        DB::table('match_items')
            ->where('id', $this->match_item->id)
            ->update([
                'status' => MatchItem::COMPLETED,
                'rant' => $qualified_count / $count,
                'count' => $count
            ]);
        Log::info('over');
    }

    public static function getQualifiedHash($conditions)
    {
        $model = DB::table('data_records')->select('bc_id');
        if (in_array('性别', $conditions)) {
            $model = $model->where('gender' , 1);
        }
        if (in_array('年龄', $conditions)) {
            $model = $model->where('age' , 1);
        }
        if (in_array('地域', $conditions)) {
            $model = $model->where('user_address' , 1);
        }
        if (in_array('行业', $conditions)) {
            $model = $model->where('industry' , 1);
        }
        if (in_array('爱好', $conditions)) {
            $model = $model->where('hobby' , 1);
        }
        if (in_array('兴趣', $conditions)) {
            $model = $model->where('interest' , 1);
        }
        if (in_array('手机品牌', $conditions)) {
            $model = $model->where('model' , 1);
        }
        if (in_array('手机号', $conditions)) {
            $model = $model->where('phone' , 1);
        }
        $bc_ids = $model->pluck('bc_id');
        return $bc_ids;
    }

    public static function getIHash($bc_ids)
    {
        $client = new Client();
        $i_hashs = [];
        foreach ($bc_ids as $bc_id) {

            $res = $client->request('GET', self::BLOCK_CHAIN_URL . $bc_id);
            $i_hashs[] = (string)$res->getBody();
        }
        return $i_hashs;
    }

    public static function getFullJson($i_hashs)
    {
        $client = new Client();
        $json_list = [];
        foreach ($i_hashs as $i_hash) {
            $res = $client->request('GET', self::IPFS_URL . $i_hash);
            $json_list[] = (string)$res->getBody();
        }
        return $json_list;
    }

    public static function checkJson($conditions, $json)
    {
        foreach ($conditions as $condition) {
            if (mb_strpos($json, $condition) === false) {
                return false;
            }
        }
        return true;
    }
}
