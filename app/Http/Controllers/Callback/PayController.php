<?php

namespace App\Http\Controllers\Callback;

use App\Facades\JhPayFacade;
use App\Facades\MemberFacade;
use App\Facades\PayFacade;
use App\Facades\RechargeFacade;
use App\Http\Controllers\Controller;
use App\Models\FinanceRecharge;
use App\Models\MemberRecharge;
use App\Repositories\MemberPlanOrderRepository;
use App\Repositories\MemberRechargeRepository;
use App\Services\AlipayService;
use App\Utils\Helper;
use App\Utils\WechatAppPay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayController extends Controller
{
    /**
     * 支付宝回调
     * @param Request $request
     */
    public function alipayCallback(Request $request,MemberRechargeRepository $rechargeRepository)
    {
        if ($request->isMethod('GET')) {
            return view('callback.pay.success');
        }
        try {
            $this->logs('支付宝回调成功了', $request->all());
            $data = $request->all();
            $order = $rechargeRepository->findBy('order_no', $data['out_trade_no']);
            if (isset($order)) {
                if ($order->status == 0) {
                    //处理
                    $order['status'] = 1;
                    $order['pay_time'] = Helper::getNowTime();
                    $ret = RechargeFacade::rechargeDeal($order);
                    if (!$ret['status']) {
                        echo '{"status":"1","msg":"失败"}';
                    }
                }
            }
            echo 'ok';
        } catch (\Exception $ex) {
            echo 'Fail';
        }

    }

    /**
     * 微信H5支付回调
     * @param Request $request
     * @param MemberRechargeRepository $rechargeRepository
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function wechatcallback(Request $request,MemberRechargeRepository $rechargeRepository)
    {
        if ($request->isMethod('GET')) {
            return view('callback.pay.success');
        }
        try {
            $this->logs('微信H5支付回调成功了', $request->all());
            $data = $request->all();
            $order = $rechargeRepository->findBy('order_no', $data['out_trade_no']);
            if (isset($order)) {
                if ($order->status == 0) {
                    //处理
                    $order['status'] = 1;
                    $order['pay_time'] = Helper::getNowTime();
                    $ret = RechargeFacade::rechargeDeal($order);
                    if (!$ret['status']) {
                        echo '{"status":"1","msg":"失败"}';
                    }
                }
            }
            echo 'ok';
        } catch (\Exception $ex) {
            echo 'Fail';
        }

    }

    /**
     * 支付宝支付
     * @param Request $request
     */
    public function alipay(Request $request)
    {
//        if ( strpos($_SERVER['HTTP_USER_AGENT'],'MicroMessenger') !== false ) {
//            $url = url("/common/alipay?no={$request->no}");
//            $html = <<<EOF
//            <!doctype html>
//                <html lang="en">
//                <head>
//                    <meta charset="UTF-8">
//                    <meta name="viewport"
//                          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
//                    <meta http-equiv="X-UA-Compatible" content="ie=edge">
//                    <title>error</title>
//                    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.4/clipboard.min.js"></script>
//                    <style>
//                        * {
//                            margin: 0;
//                            padding: 0;
//                        }
//
//                        html {
//                            height: 100%;
//                        }
//
//                        body {
//                            height: 100%;
//                        }
//
//                        .content {
//                            height: 100%;
//                        }
//
//                        .content_txt {
//                            background: #ffffff;
//                            padding: 100px 0 0 0;
//                            text-align: center;
//                        }
//
//                        .content_txt h3 {
//                            font-size:24px;
//                            font-family:PingFang SC;
//                            font-weight:500;
//                            color:rgba(34,34,34,1);
//                            margin-bottom: 30px;
//                        }
//
//                        .content_txt p {
//                            font-size:11px;
//                            font-family:PingFang SC;
//                            font-weight:400;
//                            color:rgba(102,102,102,1);
//                        }
//
//                        .content_txt a {
//                            display: block;
//                            margin-bottom: 4px;
//                            font-size:11px;
//                        }
//
//                        .btn_con {
//                            margin:  20px 30px 0 30px;
//                        }
//
//                        .btn_con button {
//                            width: 100%;
//                            border: none;
//                            text-align: center;
//                            height:40px;
//                            background:rgba(196,39,39,1);
//                            border-radius:20px;
//                            font-size:18px;
//                            font-family:PingFang SC;
//                            font-weight:500;
//                            color:rgba(255,255,255,1)
//                        }
//                    </style>
//                </head>
//                <body>
//                <div class="content">
//                    <div class="content_txt">
//                        <h3>唤醒支付宝失败！</h3>
//                        <p>请复制下面的网址到浏览器中访问</p>
//                        <a href="javascript:;">$url</a>
//                    </div>
//                    <!--    需要复制的卡号-->
//                    <div class="btn_con">
//                        <button id="btn" type="button" data-clipboard-text="$url">复制网址</button>
//                    </div>
//                </div>
//                </body>
//                </html>
//                <script>
//                    var copyBtn = new ClipboardJS('#btn');
//                    copyBtn.on("success", function (e) {
//                        alert('复制成功');
//                    });
//                    copyBtn.on("error", function (e) {
//                        console.log('复制失败，请手动复制');
//                    });
//                </script>
//EOF;
//            die($html);
//        }
        $recharge = MemberRecharge::where('order_no', $request->no)->first();
        if (!$recharge) {
            die('支付订单不存在');
        }
        if ($recharge->pay_status != 9) {
            die('订单已失效');
        }
        $alipay = new AlipayService();
        $res = $alipay->Receipt($recharge);
        if ($res['status']) {
            die($res['data']);
        } else {
            die('唤起支付失败');
        }
    }

    /**
     * 微信回调
     * @param Request $request
     */
    public function wechat(Request $request)
    {
        //支付
        $resp = PayFacade::alipay(rand(), 1);
        //查询
//        $resp = PayFacade::alipayQuery('2129209909');
        if ($resp['status']) {
            return $resp['data'];
        }
        echo '支付失败';
    }

    /*
     * 三方支付平台回调(充值)
     * */
    public function typay(Request $request, MemberRechargeRepository $rechargeRepository)
    {
        try {
            $data = $request->all();
            $this->logs('三方支付充值付款成功回调处理', $data);
            //查询订单
            $order = $rechargeRepository->findBy('order_no', $data['no']);
            if (isset($order)) {
                if ($order->status == 0) {
                    //处理
                    $order['status'] = 1;
                    $order['pay_time'] = Helper::getNowTime();
                    $ret = RechargeFacade::rechargeDeal($order);
                    if (!$ret['status']) {
                        echo '{"status":"1","msg":"失败"}';
                    }
                }
            }
            echo '{"status":"0","msg":"成功"}';
        } catch (\Exception $exception) {
            $this->logs('接收三方通知充值处理异常', $exception->getMessage());
            echo '{"status":"1","msg":"失败"}';
        }
    }

    /*
     * 三方支付平台回调(商务处理)
     * */
    public function typayBusiness(Request $request, MemberPlanOrderRepository $memberPlanOrderRepository)
    {
        try {
            $data = $request->all();
            $this->logs('三方支付商务预约付款成功回调处理', $data);
            //查询订单
            $order = $memberPlanOrderRepository->findBy('order_no', $data['out_trade_no']);
            if (isset($order)) {
                if ($order->status == 0) {
                    //处理
                    $ret = MemberFacade::dealPlanOrder($order, 1);
                    if (!$ret['status']) {
                        echo '{"status":"1","msg":"失败"}';
                    }
                }
            }
            echo '{"status":"0","msg":"成功"}';
        } catch (\Exception $exception) {
            $this->logs('接收三方通知商务预约处理异常', $exception->getMessage());
            echo '{"status":"1","msg":"失败"}';
        }
    }

    /*
    * 拼多支付平台回调(充值)
    * */
    public function pddPayRecharge(Request $request, MemberRechargeRepository $rechargeRepository)
    {
        try {

            $data = $request->all();
            $this->logs('拼多多支付充值付款成功回调处理', $data);
            if ($data['callbacks'] == 'CODE_SUCCESS') {
                //查询订单
                $order = $rechargeRepository->findBy('order_no', $data['api_order_sn']);
                if (isset($order)) {
                    if ($order->status == 0) {
                        //处理
                        $order['status'] = 1;
                        $order['pay_time'] = Helper::getNowTime();
                        $ret = RechargeFacade::rechargeDeal($order);
                        if (!$ret['status']) {
                            exit('fail');
                        }
                    }
                }
                exit('success');
            }
            exit('fail');
        } catch (\Exception $exception) {
            $this->logs('接收拼多多支付通知充值处理异常', $exception->getMessage());
            exit('fail');
        }
    }

    /*
     * 三方支付平台回调(商务处理)
     * */
    public function pddPayBusiness(Request $request, MemberPlanOrderRepository $memberPlanOrderRepository)
    {
        try {
            $data = $request->all();
            $this->logs('拼多多支付商务预约付款成功回调处理', $data);
            if ($data['callbacks'] == 'CODE_SUCCESS') {
                //查询订单
                $order = $memberPlanOrderRepository->findBy('order_no', $data['no']);
                if (isset($order)) {
                    if ($order->status == 0) {
                        //处理
                        $ret = MemberFacade::dealPlanOrder($order, 1);
                        if (!$ret['status']) {
                            exit('fail');
                        }
                    }
                }
                exit('success');
            }
            exit('fail');
        } catch (\Exception $exception) {
            $this->logs('拼多多支付通知商务预约处理异常', $exception->getMessage());
            exit('fail');
        }
    }


    /**
     * APP微信支付回调(充值)
     * @param Request $request
     */
    public function appWechatRecharge(Request $request, MemberRechargeRepository $rechargeRepository)
    {
        $wechatPay = new WechatAppPay();
        try {
            $data = $wechatPay->getNotifyData();
            $this->logs('微信APP充值付款成功回调处理', $data);

            if (!$data) {
                $wechatPay->replyNotify('FAIL', '接收回调异常');
            } else {
                //支付成功
                $out_trade_no = $data['out_trade_no'];
                //查询订单
                $order = $rechargeRepository->findBy('order_no', $out_trade_no);
                if (isset($order)) {
                    if ($order->status == 0) {
                        //处理
                        $order['status'] = 1;
                        $order['pay_time'] = Helper::getNowTime();
                        $ret = RechargeFacade::rechargeDeal($order);
                        if (!$ret['status']) {
                            $wechatPay->replyNotify('FAIL', '业务处理失败');
                        }
                    }
                }
                $wechatPay->replyNotify(); //成功结束
            }
        } catch (\Exception $exception) {
            $this->logs('接收微信APP支付通知充值处理异常', $exception->getMessage());
            $wechatPay->replyNotify('FAIL', '业务处理失败');
        }
    }

    /**
     * APP微信支付回调（商务）
     * @param Request $request
     */
    public function appWechatBusiness(Request $request, MemberPlanOrderRepository $memberPlanOrderRepository)
    {
        $wechatPay = new WechatAppPay();
        try {
            $data = $wechatPay->getNotifyData();
            $this->logs('微信APP商务付款成功回调处理', $data);

            if (!$data) {
                $wechatPay->replyNotify('FAIL', '接收回调异常');
            } else {
                //支付成功
                $out_trade_no = $data['out_trade_no'];
                //查询订单
                $order = $memberPlanOrderRepository->findBy('order_no', $out_trade_no);
                if (isset($order)) {
                    if ($order->status == 0) {
                        //处理
                        $ret = MemberFacade::dealPlanOrder($order, 1);
                        if (!$ret['status']) {
                            $wechatPay->replyNotify('FAIL', '业务处理失败');
                        }
                    }
                }
                $wechatPay->replyNotify(); //成功结束
            }
        } catch (\Exception $exception) {
            $this->logs('接收微信APP支付通知充值处理异常', $exception->getMessage());
            $wechatPay->replyNotify('FAIL', '业务处理失败');
        }
    }

}
