<?php

namespace App\Http\Controllers\System\PlatForm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 统计报表
 * User: X
 * Date: 2018/8/31
 * Time: 16:14
 */
class StatisticsController extends Controller
{

    /*
     * 日统计
     * */
    public function dayCount()
    {
        return view('system.platform.statistics.daycount');
    }

    public function getDayCount(Request $request)
    {
        $params = $request->key;
        $bdate = date('Ymd', strtotime($params['bdate']));
        $edate = date('Ymd', strtotime($params['edate']));

        $row = DB::table('member_day_count')
            ->select(DB::raw('dayint,
            ifnull(sum(rec_gold),0) as rec_gold,
            ifnull(sum(award_gold),0) as award_gold,
            ifnull(sum(profit_gold),0) as profit_gold,
            ifnull(sum(rec_money),0) as rec_money,
            ifnull(sum(take_money),0) as take_money,
            ifnull(sum(profit_money),0) as profit_money,
            ifnull(sum(consume_money),0) as consume_money,
            ifnull(sum(consume_gold),0) as consume_gold'))
            ->where('dayint', '>=', $bdate)
            ->where('dayint', '<=', $edate)
            ->groupBy('dayint')->orderByDesc('dayint')
            ->get();
        $index = 0;
        foreach ($row as $item) {
            $data[$index] = array(
                'dayint' => date("Y-m-d", strtotime($item->dayint)),
                'rec_gold' => $item->rec_gold,
                'award_gold' => $item->award_gold,
                'profit_gold' => $item->profit_gold,
                'consume_gold' => $item->consume_gold,
                'rec_money' => floatval($item->rec_money),
                'take_money' => floatval($item->take_money),
                'profit_money' => floatval($item->profit_money),
                'consume_money' => floatval($item->consume_money),
            );
            $index++;
        }
        $list = array(
            "code" => 0,
            "msg" => "",
            "count" => 100,
            "data" => $data);
        return json_encode($list);
    }
}