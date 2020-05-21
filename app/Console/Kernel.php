<?php

namespace App\Console;

use App\Facades\AliyunFacade;
use App\Facades\ImFacade;
use App\Facades\MemberFacade;
use App\Facades\RechargeFacade;
use App\Models\MemberInfo;
use App\Models\MemberPlanOrder;
use App\Models\MemberRecharge;
use App\Models\MemberTalk;
use App\Traits\ResultTrait;
use App\Utils\WechatAppPay;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    use ResultTrait;
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //定时结算持仓
//        $schedule->command('position:settle')->weekdays()->dailyAt('16:00')->before(function () {
//            // 任务即将开始...
//        })->after(function () {
//            $this->logs('定时结算持仓完成');
//        });

        /*
         * 每隔1分钟执行一次执行一次
         * */
        $schedule->call(function () {
            //定时检查是否超过2分钟未接的订单,自动结束
            $dateTime = date("Y-m-d H:i:s", strtotime("-2 minute"));
            $talkmodel = MemberTalk::where('status', '<', 2)->where('type', '>', 0)->where('updated_at', '<', $dateTime)->orderBy('updated_at', 'desc')->first();
            if (isset($talkmodel)) {
                if ($talkmodel->status == 0) {
                    MemberFacade::dealTalkOrder($talkmodel, 2); //未接听挂断
                }
                if ($talkmodel->status == 1) {
                    MemberFacade::dealTalkOrder($talkmodel, 3); //异常挂断，结束通话
                }
                MemberFacade::checkTalkOrder($talkmodel->id); //订单查询调用处理方法
//                $this->logs('超时未接听订单自动结束处理');
            }
        })->everyMinute();

        //每分钟检查主播状态未掉，IM实际是否在线
        $schedule->call(function () {
            $zbinfo = MemberInfo::where(['sex' => 1])->orderBy('updated_at', 'asc')->first();
            if (isset($zbinfo)) {
                $account = [(string)$zbinfo->id];
                list($ret, $data) = ImFacade::userStatus($account);
                if ($ret) {
                    if ($data['ActionStatus'] == "OK") {
                        $queryStatus = $data['QueryResult'][0]['State'];
                        if ($queryStatus != "Online") {
                            $zbinfo->online_status = 0;
                            Log::info('我是自动任务中的离线',[$zbinfo->nick_name]);
                            $zbinfo->vv_busy = 0;
                        } else {
                            $zbinfo->online_status = 1; //在线
                        }
                        $zbinfo->save();
                    }
                }
//                $this->logs('定时检查会员IM在线状态' . $zbinfo->nick_name, $data);
            }
        })->everyMinute();


        /*
       * 每隔1分钟执行一次，查询支付订单是超过30分钟未完成支付，自动结束。
       * */
        $schedule->call(function () {
            //定时检查是否超过2分钟未接的订单,自动结束
            $dateTime = date("Y-m-d H:i:s", strtotime("-5 minute"));
            $model = MemberRecharge::where('status', 0)->where('created_at', '<', $dateTime)->orderBy('created_at', 'desc')->first();
            if (isset($model)) {
                if ($model->way == 6) {
                    //先核单
                    //微信APP支付，查询订单
                    $wechatAppPay = new WechatAppPay();
                    $ret = $wechatAppPay->orderQuery($model->order_no);
                    if ($ret) {
                        if ($ret['trade_state'] = 'SUCCESS' && (!array_key_exists('trade_state_desc', $ret) || $ret['trade_state_desc'] == '支付成功')) {
                            //支付成功
                            $model->status = 1;
                            if (isset($ret['time_end'])) {
                                $model->pay_time = date('Y-m-d H:i:s', strtotime($ret['time_end']));
                            } else {
                                $model->pay_time = date("Y-m-d H:i:s");
                            }
                        } else {
                            $model->status = 2;
                            $model->remark = $ret['trade_state_desc'];
                        }
                    }
                } else {
                    $model->status = 2;
                    $model->remark = '超时未支付';
                }
                $ret = RechargeFacade::rechargeDeal($model);
//                $this->logs('超时未支付订单自动结束', $ret);
            }
        })->everyMinute();

        /*
        * 每隔1分钟执行一次，查询超过30分钟支付的商务订单，自动取消。
        * */
        $schedule->call(function () {
            //定时检查是否超过1分钟未接的订单,自动结束
            $dateTime = date("Y-m-d H:i:s", strtotime("-1 minute"));
            $model = MemberPlanOrder::where('pay_status', 0)->where('created_at', '<', $dateTime)->orderBy('created_at', 'desc')->first();
            if (isset($model)) {
                //自动取消订单
                MemberFacade::dealPlanOrder($model, 9);
            }
        })->everyMinute();


        /*
        * 每隔1分钟执行一次，查询超过30分钟未接的商务订单，自动拒绝。
        * */
        $schedule->call(function () {
            //定时检查是否超过2分钟未接的订单,自动结束
            $dateTime = date("Y-m-d H:i:s", strtotime("-30 minute"));
            $model = MemberPlanOrder::where('status', 1)->where('updated_at', '<', $dateTime)->orderBy('updated_at', 'desc')->first();
            if (isset($model)) {
                //自动拒绝订单
                MemberFacade::dealPlanOrder($model, 3, 3);
            }
        })->everyMinute();

        /**
         * 每天凌晨定时检测
         * 提交过实名认证
         * 但是没有点击返回的信息
         */
//        $schedule->call(function () {
//            $realinfos = MemberInfo::where('realname_id','<>','0')->whereDoesntHave('realname')->pluck('realname_id');
//            $realinfos = json_decode($realinfos);
//            foreach ($realinfos as $realinfo){
//                AliyunFacade::DescribeVerifyResult($realinfo);
//            }
//            self::logs('定时检测实名认证用户信息');
//        })->dailyAt('00:00');
    }


    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
