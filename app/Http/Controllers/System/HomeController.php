<?php

namespace App\Http\Controllers\System;

use App\Facades\AliyunFacade;
use App\Facades\CommonFacade;
use App\Facades\ImFacade;
use App\Facades\OssFacade;
use App\Facades\PayFacade;
use App\Facades\SmsFacade;
use App\Http\Controllers\Controller;
use App\Models\MemberAccount;
use App\Models\MemberBusiness;
use App\Models\MemberDayCount;
use App\Models\MemberFile;
use App\Models\MemberIdea;
use App\Models\MemberInfo;
use App\Models\MemberRealName;
use App\Models\MemberReport;
use App\Models\MemberSelfie;
use App\Models\MemberTakeNow;
use App\Models\SystemConfig;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HomeController extends Controller
{
    /**
     * 页面首页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('system.index');
    }
    public function info()
    {
        return view('system.info');
    }
    /**
     * 工作台
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function home()
    {
        //会员账户统计
        $memberAccount = MemberAccount::whereHas('member', function ($query) {
            $query->where(['status' => 1, 'sex' => 0]);
        });
        $surplus_gold = $memberAccount->sum('surplus_gold'); //剩余金币
        $notuse_gold = $memberAccount->sum('notuse_gold'); //冻结不可用
        $surplus_rmb = $memberAccount->sum('surplus_rmb'); //金额
        $notuse_rmb = $memberAccount->sum('notuse_rmb'); //不可提现金额

        //主播账户统计
        $zbAccount = MemberAccount::whereHas('member', function ($query) {
            $query->where(['status' => 1, 'sex' => 1]);
        });
        $zb_surplus_gold = $zbAccount->sum('surplus_gold'); //剩余金币
        $zb_notuse_gold = $zbAccount->sum('notuse_gold'); //冻结不可用
        $zb_surplus_rmb = $zbAccount->sum('surplus_rmb'); //金额
        $zb_notuse_rmb = $zbAccount->sum('notuse_rmb'); //不可提现金额

        //昨日
        $dayInt = date("Ymd", strtotime("-1 day"));
        $yestodayCountArray = MemberDayCount::where(['dayint' => $dayInt])->first(array(
            DB::raw('ifnull(sum(rec_money),0) as rec_money'), //充值金额
            DB::raw('ifnull(sum(take_money),0) as take_money'), //提现金额
            DB::raw('ifnull(sum(consume_gold),0) as consume_gold'), //会员消费金币
            DB::raw('ifnull(sum(profit_gold),0) as profit_gold'), //主播收益金币
            DB::raw('ifnull(sum(consume_gold),0)-ifnull(sum(profit_gold),0) as sys_gold'), //平台收益金币
        ))->toArray();

        //今日
        $dayInt = date('Ymd');
        $todayCountArray = MemberDayCount::where(['dayint' => $dayInt])->first(array(
            DB::raw('ifnull(sum(rec_money),0) as rec_money'), //充值金额
            DB::raw('ifnull(sum(take_money),0) as take_money'), //提现金额
            DB::raw('ifnull(sum(consume_gold),0) as consume_gold'), //会员消费金币
            DB::raw('ifnull(sum(profit_gold),0) as profit_gold'), //主播收益金币
            DB::raw('ifnull(sum(consume_gold),0)-ifnull(sum(profit_gold),0) as sys_gold'), //平台收益金币
        ))->toArray();

        //累计
        $dayInt = date("Ymd", strtotime("-1 day"));
        $totalCountArray = MemberDayCount::where('dayint', '<=', $dayInt)->first(array(
            DB::raw('ifnull(sum(rec_money),0) as rec_money'), //充值金额
            DB::raw('ifnull(sum(take_money),0) as take_money'), //提现金额
            DB::raw('ifnull(sum(consume_gold),0) as consume_gold'), //会员消费金币
            DB::raw('ifnull(sum(profit_gold),0) as profit_gold'), //主播收益金币
            DB::raw('ifnull(sum(consume_gold),0)-ifnull(sum(profit_gold),0) as sys_gold'), //平台收益金币
        ))->toArray();

        //待处理
        $selfie = MemberSelfie::where('status', 0)->count();
        $business = MemberBusiness::where('status', 0)->count();
        $realname = MemberRealName::where('status', 0)->count();
        $files = MemberFile::where('status', 0)->count();
        $takenow = MemberTakeNow::where('status', 0)->count();
        $report = MemberReport::where('status', 0)->count(); //举报
        $idea = MemberIdea::where('status', 0)->count(); //意见反馈
        $head = MemberInfo::whereNotNull('new_head_pic' )->where('new_head_pic','<>','')->count(); //待审头像

        //返回结果
        $count = array(
            'member_account' => array(
                'surplus_gold' => $surplus_gold,
                'notuse_gold' => $notuse_gold,
                'surplus_rmb' => floatval($surplus_rmb),
                'notuse_rmb' => floatval($notuse_rmb),
            ),
            'zb_account' => array(
                'surplus_gold' => $zb_surplus_gold,
                'notuse_gold' => $zb_notuse_gold,
                'surplus_rmb' => floatval($zb_surplus_rmb),
                'notuse_rmb' => floatval($zb_notuse_rmb),
            ),
            'today' => $todayCountArray,
            'yestoday' => $yestodayCountArray,
            'total' => $totalCountArray,
            'check' => array(
                'selfie' => $selfie,
                'business' => $business,
                'realname' => $realname,
                'files' => $files,
                'takenow' => $takenow,
                'report' => $report,
                'idea' => $idea,
                'head' => $head,
            )
        );

        return view('system.home', compact('count'));
    }

    /**
     * 测试方法
     */
    public function test()
    {
            $realinfos = MemberInfo::where('realname_id','<>','0')->whereDoesntHave('realname')->pluck('realname_id');
            $realinfos = json_decode($realinfos);
            dd($realinfos);
            foreach ($realinfos as $realinfo){
                dump(AliyunFacade::DescribeVerifyResult($realinfo));

            }
            dd('完毕');
//        $arr = array("blue","red","green","yellow");
//        $str1 = str_replace("red","pink",$arr,$i);
//        print_r($str1);
//        $trs = str_replace('高','*','高跟鞋');
//        dd($trs);
        if (Cache::has('SystemConfig')) {
            $config = Cache::get('SystemConfig')->keyword;
        } else {
            $config = SystemConfig::first()->keyword;
        }
        $trs = '高跟鞋';
        if (isset($config)) {
            $a = explode(',', $config);
            foreach ($a as $item) {
                if (strpos('高跟鞋', $item) !== false){
                    $trs = str_replace($item,'*','高跟鞋');
                    dd($trs);
                }
            }
        }
        dd($trs);

        dd(Cache::get('id'));
        dd(ImFacade::DissolveRoom(3551));
        dd(OssFacade::putUrl('https://cloudauth-zhangjiakou.oss-cn-zhangjiakou.aliyuncs.com/prod/hammal/1748562221058490/dbfe11afaa07445aa8f6c23ad0f7aa3a/p-4d09c990d974484493fa66c9e46f7dab?Expires=1576132108&OSSAccessKeyId=H4sp5QfNbuDghquU&Signature=cIo5RYG4o%2BWX0%2Bjv7fT2t8ghUOY%3D'));
//        dd(AliyunFacade::DescribeVerifyToken('10014', '512322198112204917', '234234'));
        dd(AliyunFacade::DescribeVerifyResult('350'));
//        //支付
//        $resp = PayFacade::alipay(rand(), 1);
//        //查询
////        $resp = PayFacade::alipayQuery('2129209909');
//        if ($resp['status']) {
//            return $resp['data'];
//        }


    }

}
