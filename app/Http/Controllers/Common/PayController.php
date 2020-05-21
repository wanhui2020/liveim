<?php

namespace App\Http\Controllers\Common;

use App\Facades\JhFacade;
use App\Facades\JhPayFacade;
use App\Facades\PayFacade;
use App\Facades\RechargeFacade;
use App\Http\Controllers\Controller;
use App\Models\FinanceRecharge;
use App\Models\MemberRecharge;
use App\Repositories\MemberRechargeRepository;
use App\Services\AlipayService;
use App\Services\FuiouPayService;
use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yansongda\LaravelPay\Facades\Pay;

class PayController extends Controller
{
    /**
     * 聚合支付回调
     * @param Request $request
     * @return array
     */
    public function juhepayback(Request $request,MemberRechargeRepository $rechargeRepository)
    {
        try {
            $data = $request->all();
            $this->logs('聚合支付回调返回成功',$request->all());
            //查询订单
            $order = $rechargeRepository->findBy('order_no', $data['Payid']);
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
            echo 'OK';
        } catch (\Exception $exception) {
            $this->logs('接收三方通知充值处理异常', $exception->getMessage());
            echo '{"status":"1","msg":"失败"}';
        }
    }
    public function alipays(Request $request)
    {
        try{
            $no  = $request->no;
            $order = MemberRecharge::where('order_no',$no)->first();
            if (!isset($order)){
                return $this->failure('1','支付订单不存在！');
            }
            $title = '充值金币'; //交易金额
            return PayFacade::alipay($no,$order->amount,$title);
        }catch (\Exception $ex){
            return $this->exception($ex,'支付错误');
        }
    }

    /**
     *  微信H5支付
     * @param Request $request
     * @return array
     */
    public function wechath5(Request $request)
    {
        try{
            $no  = $request->no;
            $order = MemberRecharge::where('order_no',$no)->first();
            if (!isset($order)){
                return $this->failure('1','支付订单不存在！');
            }
            $title = '充值金币'; //交易金额
            return PayFacade::wechatH5($no,$order->amount,$title);
        }catch (\Exception $ex){
            return $this->exception($ex,'支付错误');
        }
    }
    /**
     * 支付宝支付
     */
    public function alipay(Request $request,MemberRechargeRepository $rechargeRepository)
    {
        try{
            //支付宝支付
            //支付宝支付参数
            /**
             * out_trade_no     订单号
             * total_amount    订单金额，单位：元
             * subject    订单商品标题
             */
            //订单相关参数
            $orderNo = $request->no;
            if (!isset($orderNo)){
                return $this->validation('缺少支付编号!');
            }
            $recharge = MemberRecharge::where('order_no',$orderNo)->where('status',0)->first();
            if (!isset($recharge)){
                return $this->validation('该订单已支付，或订单不存在!');
            }
            $config_biz = [
                'out_trade_no' => $recharge->order_no,
                'total_amount' =>$recharge->amount ,
                'subject'      => '充值金币',
            ];
            //调用方法
            $alipay = Pay::alipay()->wap($config_biz);
            $alipay->send();
        } catch (\Exception $exception) {
            $this->logs('发起支付宝支付失败', $exception->getMessage());
            return $this->validation('操作失败', $exception->getMessage());
        }
    }
    /**
     * 支付宝回调接口
     */
    public function alipaycallback(Request $request,MemberRechargeRepository $rechargeRepository)
    {
        try {
            $alipay = Pay::alipay();
            $data = $alipay->verify();
            $this->logs('支付宝支付回调返回成功',$data->all());
            echo "success";
            return [];
            //查询订单
            /*$order = $rechargeRepository->findBy('order_no', $data['orderid']);
            Log::info('支付订单',[$order]);
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
            echo 'OK';*/
        } catch (\Exception $exception) {
            $this->logs('接收三方通知充值处理异常', $exception->getMessage());
            echo '{"status":"1","msg":"失败"}';
        }
    }

    /**
     * 恒云支付回调
     */
    public function hypayback(Request $request,MemberRechargeRepository $rechargeRepository)
    {
        try{
            $data = $request->all();
            $this->logs('恒云支付回调返回成功',$data);
            //查询订单
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
            echo 'success';

        } catch (\Exception $exception) {
            $this->logs('接收恒云支付通知充值处理异常', $exception->getMessage());
            echo '{"status":"1","msg":"失败"}';
        }
    }

    /**
     * 铭科支付回调
     */
    public function mkpayback(Request $request,MemberRechargeRepository $rechargeRepository)
    {
        try{
            $data = $request->all();
            $this->logs('铭科支付回调返回成功',$data);
            //查询订单
            $order = $rechargeRepository->findBy('order_no', $data['orderNo']);
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
            echo 'success';

        } catch (\Exception $exception) {
            $this->logs('接收铭科支付通知充值处理异常', $exception->getMessage());
            echo '{"status":"1","msg":"失败"}';
        }
    }
    /**
     * 聚合支付接口
     */
    public function juhepay()
    {
        try{
            $jhpay = JhFacade::jhPay();
            if (preg_match('/window\.location\.href=\'([\s\S]*?)\';/',$jhpay,$arr)){
                if (isset($arr[1])){
                    return $this->succeed($arr[1],'聚合支付链接返回成功!');
                }
                return $this->failure($arr,'第三方支付未返回支付链接!');
            }
            return $this->failure($jhpay,'第三方支付返回失败!');
        } catch (\Exception $e) {
            $this->exception($e);
            return $this->validation('支付异常，请联系管理员');
        }
    }

    /**
     * 聚合支付主动查询接口
     */
    public function juhequery(Request $request)
    {
        try{
            $data = $request->all();
            $jhpay = JhFacade::jhpayquery($data);
            return $jhpay;
        } catch (\Exception $e) {
            $this->exception($e);
            return $this->validation('聚合支付主动查询失败，请联系管理员');
        }
    }
}
