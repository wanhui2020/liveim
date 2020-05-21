<?php

namespace App\Http\Controllers\Api\Member;

use App\Facades\HyFacade;
use App\Facades\JhFacade;
use App\Facades\MkFacade;
use App\Facades\PayFacade;
use App\Http\Controllers\Api\ApiController;
use App\Models\MemberAccount;
use App\Repositories\MemberRechargeRepository;
use App\Utils\Helper;
use App\Utils\WechatAppPay;
use Illuminate\Http\Request;
use Yansongda\LaravelPay\Facades\Pay;

/*
 * 会员充值管理
 * */

class MemberRechargeController extends ApiController
{

    public function __construct(MemberRechargeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 充值金币
     * @param Request $request
     * @return array
     */
    public function rechargeGold(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $memberId = $member->id;
            $way = $request['way']; //支付方式
            $amount = $request['amount']; //支付金额
            $gold = $request['gold']; //金币数量
            $give = $request['give']; //普通会员赠送金币
            $vip_give = $request['vip_give']; //vip会员赠送金币
            //判断参数
            if (!isset($way) || !isset($amount) || !isset($gold) || !isset($give) || !isset($vip_give)) {
                return $this->validation('请输入所有必填参数！');
            }
            $data['member_id'] = $memberId;
            $data['order_no'] = Helper::getNo(); //编号
            $data['type'] = 0; //充值金币
            $data['way'] = $way;
            $data['amount'] = $amount;
            $data['quantity'] = $gold; //金币数量

            $isvip = MemberAccount::where(['member_id' => $memberId])->first()->is_vip;
            if ($isvip) {
                $data['vip_give'] = $vip_give;
            } else {
                $data['give'] = $give;
            }
            //保存
            $result = $this->repository->store($data);
            if (!$result['status']) {
                return $this->failure(1, '操作失败,请稍后重试！', $result['data']);
            }
            $orderNo = $data['order_no'];
            //调用微信原生支付
            if ($way == 6) {
                /**
                 * 微信之前参数
                 * $appid wxf40312b43b92191c
                 * $mch_id 1558282021
                 * $key  zhaocheng11637501641762365756888
                 */
                /**
                 * 现在的参数
                 * 1569471791
                 * wx9481d219a0f48687
                 */
                //微信支付
                //1.统一下单方法
                $payAmount = $amount * 100;
                $appid = "wxf40312b43b92191c";
                $mch_id = "1558282021";
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/callback/pay/appwechat/recharge';
                $key = "zhaocheng11637501641762365756888";//
                $wechatAppPay = new WechatAppPay($appid, $mch_id, $notify_url, $key);
                $params['body'] = '会员金币充值';                       //商品描述
                $params['out_trade_no'] = $orderNo;    //自定义的订单号
                $params['total_fee'] = $payAmount;                       //订单金额 只能为整数 单位为分
                $params['trade_type'] = 'APP';                      //交易类型 JSAPI | NATIVE | APP | WAP
                $result = $wechatAppPay->unifiedOrder($params);

                //2.创建APP端预支付参数
                /** @var TYPE_NAME $result */
                $data = @$wechatAppPay->getAppPayParams($result['prepay_id']);
                //下边为了拼够参数，多拼了几个给安卓、ios前端
//                $data['body'] = '会员金币充值';
//                $data['notify_url'] = $notify_url;
//                $data['total_fee'] = $payAmount;
//                $data['success'] = 1;
//                $data['spbill_create_ip'] = '127.0.0.1';
//                $data['out_trade_no'] = $orderNo;
//                $data['trade_type'] = 'APP';
                //返回值
                $payParam = array(
                    'appid' => $data['appid'],
                    'partnerid' => $mch_id,
                    'out_trade_no' => $orderNo,
                    'prepayId' => $data['prepayid'],
                    'package' => $data['package'],
                    'nonce_str' => $data['noncestr'],
                    'timestamp' => $data['timestamp'],
                    'sign' => $data['sign'],
                );
            }
            if ($way == 3) {
                //三方通易支付，拼接支付地址
                $payParam = PayFacade::typay($data['order_no'], $amount, $member->code);
            }
            if ($way == 1) {
                //拼多支付(微信)
                $res = PayFacade::pddPay($data['order_no'], $amount);

                if (!array_key_exists('code', $res) || $res['code'] != 200) {
                    return $this->validation($res['msg']);
                }
                $payParam = $res['data']['h5_url'];
            }
            if ($way == 2) {
                //拼多支付(支付宝)
                $res = PayFacade::pddPay($data['order_no'], $amount, 'alipay');

                if (!array_key_exists('code', $res) || $res['code'] != 200) {
                    return $this->validation($res['msg']);
                }
                $payParam = $res['data']['h5_url'];
            }
            if ($way == 8){
                //聚合支付(支付宝H5)
                $params['out_trade_no'] = $orderNo; //订单号
                $params['amount'] = $amount; //交易金额
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/common/hypayback';
                $params['notify_url'] = $notify_url; //恒云支付1
                $jhpay = JhFacade::jhPay($params);
                if ($jhpay['status']){
                    return $this->succeed($jhpay['data'],'聚合支付链接返回成功!');
                }
                return $this->failure($jhpay,'第三方支付返回失败!');
            }
            if ($way == 9) { //支付宝支付
//                $title = '充值金币'; //交易金额
//                return PayFacade::alipay($orderNo,$amount,$title);
                return $this->succeed( 'http://' . $_SERVER['HTTP_HOST'] . '/common/alipays?no=' . $orderNo, '支付宝发起支付成功');
            }
            if ($way == 20) { //微信H5支付
                return $this->succeed( 'http://' . $_SERVER['HTTP_HOST'] . '/common/wechath5?no=' . $orderNo, '微信H5支付发起成功');
            }
            if($way == 10 || $way == 11 || $way == 12 || $way == 13 || $way == 14){
                //恒云支付
                $params['out_trade_no'] = $orderNo; //订单号
                $params['amount'] = $amount; //交易金额
                $params['way'] = $way; //恒云支付1
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/common/hypayback';
                $params['notify_url'] = $notify_url; //恒云支付1
                $jhpay = HyFacade::hyPay($params);
                return $this->succeed($jhpay,'恒云支付链接返回成功!');
            }
            if($way == 22){
                //恒云微信
                $params['out_trade_no'] = $orderNo; //订单号
                $params['amount'] = $amount; //交易金额
                $params['way'] = $way; //恒云支付1
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/common/hypayback';
                $params['notify_url'] = $notify_url; //恒云支付1
                $jhpay = HyFacade::hyPay($params);
                return $this->succeed($jhpay,'恒云支付链接返回成功!');
            }
            if($way == 13){
                //铭科支付
                $params['orderNo'] = $orderNo; //订单号
                $params['money'] = $amount; //交易金额
                $params['way'] = $way; //铭科支付
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/common/mkpayback';
                $params['notifyUrl'] = $notify_url; //回调地址
                $params['productName'] = '会员金币充值';                       //商品描述
                $jhpay = MkFacade::mkPay($params);
                return $this->succeed($jhpay,'铭科支付链接返回成功!');
            }
            if($way == 21){
                //汇潮支付  一麻袋支付
                $params['orderNo'] = $orderNo; //订单号
                $params['money'] = $amount; //交易金额
                $params['way'] = $way; //汇潮支付  一麻袋支付
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/common/mkpayback';
                $params['notifyUrl'] = $notify_url; //回调地址
                $params['productName'] = '会员金币充值';                       //商品描述
                $jhpay = HCFacade::hcPay($params);
                return $this->succeed($jhpay,'汇潮支付链接返回成功!');
            }
            return $this->succeed($payParam, '创建订单成功，跳转支付...');
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /**
     * 充值VIP
     * @param Request $request
     * @return array
     */
    public function rechargeVIP(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $memberId = $member->id;
            $way = $request['way']; //支付方式
            $amount = $request['amount']; //支付金额
            $days = $request['days']; //天数
            $remark = $request['remark']; //备注
            //判断参数
            if (!isset($way) || !isset($amount) || !isset($days)) {
                return $this->validation('请输入所有必填参数！');
            }
            if ($days <= 0) {
                return $this->validation('天数参数错误！');
            }
            $data['member_id'] = $memberId;
            $data['order_no'] = Helper::getNo(); //编号
            $data['type'] = 2; //充值VIP
            $data['way'] = $way;
            $data['amount'] = $amount;
            $data['quantity'] = $days; //vip天数
            $data['remark'] = $remark; //备注说明

            //保存
            $result = $this->repository->store($data);
            if (!$result['status']) {
                return $this->failure(1, '操作失败,请稍后重试！', $result['data']);
            }

            $orderNo = $data['order_no'];
            if ($way == 3) {
                //三方通易支付，拼接支付地址
                $payParam = PayFacade::typay($orderNo, $amount, $member->code);
            }
            if ($way == 1) {
                //拼多支付(微信)
                $res = PayFacade::pddPay($orderNo, $amount);

                if (!array_key_exists('code', $res) || $res['code'] != 200) {
                    return $this->validation($res['msg']);
                }
                $payParam = $res['data']['h5_url'];
            }
            if ($way == 2) {
                //拼多支付(支付宝)
                $res = PayFacade::pddPay($orderNo, $amount, 'alipay');

                if (!array_key_exists('code', $res) || $res['code'] != 200) {
                    return $this->validation($res['msg']);
                }
                $payParam = $res['data']['h5_url'];
            }
            //调用微信原生支付
            if ($way == 6) {
                //微信支付
                //1.统一下单方法
                $payAmount = $amount * 100;
                $appid = "wxf40312b43b92191c";
                $mch_id = "1558282021";
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/callback/pay/appwechat/recharge';
                $key = "zhaocheng11637501641762365756888";//
                $wechatAppPay = new WechatAppPay($appid, $mch_id, $notify_url, $key);
                $params['body'] = '会员金币充值';                       //商品描述
                $params['out_trade_no'] = $orderNo;    //自定义的订单号
                $params['total_fee'] = $payAmount;                       //订单金额 只能为整数 单位为分
                $params['trade_type'] = 'APP';                      //交易类型 JSAPI | NATIVE | APP | WAP
                $result = $wechatAppPay->unifiedOrder($params);

                //2.创建APP端预支付参数
                /** @var TYPE_NAME $result */
                $data = @$wechatAppPay->getAppPayParams($result['prepay_id']);
                //下边为了拼够参数，多拼了几个给安卓、ios前端
//                $data['body'] = '会员金币充值';
//                $data['notify_url'] = $notify_url;
//                $data['total_fee'] = $payAmount;
//                $data['success'] = 1;
//                $data['spbill_create_ip'] = '127.0.0.1';
//                $data['out_trade_no'] = $orderNo;
//                $data['trade_type'] = 'APP';
//                $payParam = $data;
                $payParam = array(
                    'appid' => $data['appid'],
                    'partnerid' => $mch_id,
                    'out_trade_no' => $orderNo,
                    'prepayId' => $data['prepayid'],
                    'package' => $data['package'],
                    'nonce_str' => $data['noncestr'],
                    'timestamp' => $data['timestamp'],
                    'sign' => $data['sign'],
                );
            }
            if ($way == 8){
                //聚合支付(支付宝H5)
                $params['out_trade_no'] = $orderNo; //订单号
                $params['amount'] = $amount; //交易金额
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/common/hypayback';
                $params['notify_url'] = $notify_url; //恒云支付1
                $jhpay = JhFacade::jhPay($params);
                if ($jhpay['status']){
                    return $this->succeed($jhpay['data'],'聚合支付链接返回成功!');
                }
                return $this->failure($jhpay,'第三方支付返回失败!');
//                if (preg_match('/window\.location\.href=\'([\s\S]*?)\';/',$jhpay,$arr)){
//                    if (isset($arr[1])){
//                        return $this->succeed($arr[1],'聚合支付链接返回成功!');
//                    }
//                    return $this->failure($arr,'第三方支付未返回支付链接!');
//                }
            }
            if ($way == 9) {
                return $this->succeed( 'http://' . $_SERVER['HTTP_HOST'] . '/common/alipays?no=' . $orderNo, '支付宝发起支付成功');
            }
            if($way == 10 || $way == 11 || $way == 12){
                //恒云支付
                $params['out_trade_no'] = $orderNo; //订单号
                $params['amount'] = $amount; //交易金额
                $params['way'] = $way; //恒云支付1
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/common/hypayback';
                $params['notify_url'] = $notify_url; //恒云支付1
                $jhpay = HyFacade::hyPay($params);
                return $this->succeed($jhpay,'恒云支付链接返回成功!');
            }
            if($way == 22){
                //恒云微信
                $params['out_trade_no'] = $orderNo; //订单号
                $params['amount'] = $amount; //交易金额
                $params['way'] = $way; //恒云支付1
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/common/hypayback';
                $params['notify_url'] = $notify_url; //恒云支付1
                $jhpay = HyFacade::hyPay($params);
                return $this->succeed($jhpay,'恒云支付链接返回成功!');
            }
            if($way == 13){
                //铭科支付
                $params['orderNo'] = $orderNo; //订单号
                $params['money'] = $amount; //交易金额
                $params['way'] = $way; //铭科支付
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/common/mkpayback';
                $params['notifyUrl'] = $notify_url; //回调地址
                $params['productName'] = '充值VIP';                       //商品描述
                $jhpay = MkFacade::mkPay($params);
                return $this->succeed($jhpay,'铭科支付链接返回成功!');
            }
            return $this->succeed($payParam, '创建订单成功，跳转支付...');
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     * 充值订单发起支付
     * */
    public function rechargePay(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $memberId = $member->id;
            $orderno = $request['order_no']; //订单编号
            if (!isset($orderno)) {
                return $this->validation('请输入所有必填参数！');
            }
            $model = $this->repository->findBy('order_no', $orderno);
            if (!isset($model)) {
                return $this->validation('订单不存在！');
            }
            if ($model->status != 0) {
                return $this->validation('订单不能再进行支付！');
            }
            $way = $model->way; //支付方式
            $amount = $model->amount; //金额
            //调用微信原生支付
            if ($way == 6) {
                //微信支付
                //1.统一下单方法
                $payAmount = $amount * 100; //转换成分
                $appid = "wxf40312b43b92191c";
                $mch_id = "1558282021";
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/callback/pay/appwechat/recharge';
                $key = "zhaocheng11637501641762365756888";//
                $wechatAppPay = new WechatAppPay($appid, $mch_id, $notify_url, $key);
                $params['body'] = '会员金币充值';                       //商品描述
                $params['out_trade_no'] = $orderno;    //自定义的订单号
                $params['total_fee'] = $payAmount;                       //订单金额 只能为整数 单位为分
                $params['trade_type'] = 'APP';                      //交易类型 JSAPI | NATIVE | APP | WAP
                $result = $wechatAppPay->unifiedOrder($params);

                //2.创建APP端预支付参数
                /** @var TYPE_NAME $result */
                $data = @$wechatAppPay->getAppPayParams($result['prepay_id']);
                //下边为了拼够参数，多拼了几个给安卓、ios前端
//                $data['body'] = '会员金币充值';
//                $data['notify_url'] = $notify_url;
//                $data['total_fee'] = $payAmount;
//                $data['success'] = 1;
//                $data['spbill_create_ip'] = '127.0.0.1';
//                $data['out_trade_no'] = $orderno;
//                $data['trade_type'] = 'APP';
//                $payParam = $data;
                $payParam = array(
                    'appid' => $data['appid'],
                    'partnerid' => $mch_id,
                    'out_trade_no' => $orderno,
                    'prepayId' => $data['prepayid'],
                    'package' => $data['package'],
                    'nonce_str' => $data['noncestr'],
                    'timestamp' => $data['timestamp'],
                    'sign' => $data['sign'],
                );
            }
            if ($way == 3) {
                //三方通易支付，拼接支付地址
                $payParam = PayFacade::typay($orderno, $amount, $member->code);
            }
            if ($way == 1) {
                //拼多支付(微信)
                $res = PayFacade::pddPay($orderno, $amount);

                if (!array_key_exists('code', $res) || $res['code'] != 200) {
                    return $this->validation($res['msg']);
                }
                $payParam = $res['data']['h5_url'];
            }
            if ($way == 2) {
                //拼多支付(支付宝)
                $res = PayFacade::pddPay($orderno, $amount, 'alipay');

                if (!array_key_exists('code', $res) || $res['code'] != 200) {
                    return $this->validation($res['msg']);
                }
                $payParam = $res['data']['h5_url'];
            }
            if ($way == 8){
                //聚合支付(支付宝H5)
                $params['out_trade_no'] = $orderno; //订单号
                $params['amount'] = $amount; //交易金额
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/common/hypayback';
                $params['notify_url'] = $notify_url; //恒云支付1
                $jhpay = JhFacade::jhPay($params);
                if ($jhpay['status']){
                    return $this->succeed($jhpay['data'],'聚合支付链接返回成功!');
                }
                return $this->failure($jhpay,'第三方支付返回失败!');
            }
            if ($way == 9) {
                return $this->succeed( 'http://' . $_SERVER['HTTP_HOST'] . '/common/alipays?no=' . $orderno, '支付宝发起支付成功');
            }
            if($way == 10 || $way == 11 || $way == 12){
                //恒云支付
                $params['out_trade_no'] = $orderno; //订单号
                $params['amount'] = $amount; //交易金额
                $params['way'] = $way; //恒云支付1
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/common/hypayback';
                $params['notify_url'] = $notify_url; //恒云支付1
                $jhpay = HyFacade::hyPay($params);
                return $this->succeed($jhpay,'恒云支付链接返回成功!');
            }
            if($way == 22){
                //恒云微信
                $params['out_trade_no'] = $orderno; //订单号
                $params['amount'] = $amount; //交易金额
                $params['way'] = $way; //恒云支付1
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/common/hypayback';
                $params['notify_url'] = $notify_url; //恒云支付1
                $jhpay = HyFacade::hyPay($params);
                return $this->succeed($jhpay,'恒云支付链接返回成功!');
            }
            if($way == 13){
                //铭科支付
                $params['orderNo'] = $orderno; //订单号
                $params['money'] = $amount; //交易金额
                $params['way'] = $way; //铭科支付
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/common/mkpayback';
                $params['notifyUrl'] = $notify_url; //回调地址
                $params['productName'] = '会员支付';                       //商品描述
                $jhpay = MkFacade::mkPay($params);
                return $this->succeed($jhpay,'铭科支付链接返回成功!');
            }
            return $this->succeed($payParam, '跳转支付...');
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }
}
