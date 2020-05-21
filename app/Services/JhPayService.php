<?php

namespace App\Services;

use App\Models\CustomerWallet;
use App\Models\FinanceRecharge;
use App\Models\FinanceRecord;
use App\Models\PlatformPayment;
use App\Traits\ResultTrait;

//聚合支付
class JhPayService
{
    use ResultTrait;
    private $website;//授权域名
    private  $wgurl = 'http://ceshi3.aaaen.top'; //网关地址
    private  $pay_memberid = '200000111'; //商户号  200000111
    private  $shkey = 'dsafasdxcvczzxcxcxczadfwreqweqwe'; //商户key
    private  $pay_type = '2'; //支付方式

    //聚合支付
    public function jhPay($params)
    {
        try {
            if ($params['out_trade_no'] == '') {
                return $this->failure(1, '没有订单号,请稍后重试！', $params);
            }
            if ($params['amount'] == '') {
                return $this->failure(1, '没有支付金额,请稍后重试！', $params);
            }
            $parameters = $this->payParameters($params);  //获取支付参数
            $sign = $this->signs($parameters);
            $url = $this->wgurl.'/Pay'; //网关地址
            $response = $this->post($url, $sign);
            $res = json_decode($response);
            if ($res->code == 1){
                return $this->succeed($res->href);
            }
            return $this->validation($res->msg);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     *  聚合支付主动查询
     *  pay_memberid    商户编号
     *  pay_orderid      商户订单号
     *  pay_md5sign    MD5签名
     */
    public function jhpayquery($info)
    {
        try {
            $pay_memberid = $this->pay_memberid;   //商户后台API管理获取  商户号
            $url = $this->wgurl.'/Pay_Trade_query.html'; //网关地址
            $pay_orderid = $info['pay_orderid'];
            $pay_md5sign = $info['pay_md5sign'];
            $data = [
                'pay_memberid' => $pay_memberid,
                'pay_orderid' => $pay_orderid,
                'pay_md5sign' => $pay_md5sign,
            ];
            $response = $this->post($url, $data);
            return $response;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    private function post($url, $params)
    {
        // 设置来源
        $referer = $this->website;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        // 模拟来源
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        $response = curl_exec($ch);
        if ($error = curl_error($ch)) {
            die($error);
        }
        curl_close($ch);
        return $response;
    }


    public function signs($params)
    {
        try {
            //参数中含有sign
            if (isset($params['api_token']) || empty($params['api_token'])) {
                unset($params['api_token']);
            }
            $params['sign'] = md5($params['payId'].$params['type'].$params['price'].md5($this->pay_memberid .$this->shkey)); //商户后台API管理获取
            return $params;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }
    /**
     * 聚合支付签名
     */
    public function sign($params)
    {
        try {
            //参数中含有sign
            if (isset($params['api_token']) || empty($params['api_token'])) {
                unset($params['api_token']);
            }
            $Md5key = $this->shkey; //商户后台API管理获取
            if (count($params) >= 1) {
                //参数字典排序
                ksort($params);
                $str = '';
                foreach ($params as $key => $val) {
                    $str = $str . $key . "=" . $val . "&";
                }
                $sign = strtoupper(md5($str . "key=" . $Md5key));
            }
            $params["pay_md5sign"] = $sign;
            $params['pay_attach'] = "1234|456";
            $params['pay_productname'] = '团购商品';
            return $params;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     *  提取支付参数
     * @return array
     */
    public function payParameters($params)
    {
        try {
            // 支付参数
            $pay_memberid = $this->pay_memberid;   //商户后台API管理获取  商户号
            $pay_orderid = $params['out_trade_no'];    //订单号
            $pay_amount = $params['amount'];    //交易金额
            $pay_applydate = date("Y-m-d H:i:s");  //订单时间
            if ($params['notify_url']){
                $pay_notifyurl = $params['notify_url'];	//	通知地址
            }else{
                $pay_notifyurl = 'http://120.24.250.152/common/juhepayback';   //服务端返回地址 http://demo.weiyou8888.cn/common/juhepayback
            }
            $pay_callbackurl = 'http://120.24.250.152/common/juhepay';  //页面跳转返回地址
            $pay_bankcode = $this->pay_type; //支付宝H5  支付方式
            $native = array(
                "Merchant" => $pay_memberid, //商户号
                "payId" => $pay_orderid, //订单号
                "price" => $pay_amount, //交易金额
//                "pay_applydate" => $pay_applydate,//订单时间
                "type" => $pay_bankcode, // 支付方式
//                "pay_notifyurl" => $pay_notifyurl,
//                "pay_callbackurl" => $pay_callbackurl,
            );
            return $native;
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }
}
