<?php

namespace App\Services;
use App\Traits\ResultTrait;

//铭科支付
class MkPayService
{
    use ResultTrait;
    private $website;//授权域名
    private $wgurl = 'http://www.minkjszf.xyz'; //网关url
    private $appid = '19123015084210002'; //商户号
    private $key = '8305fd2dabea40f49d31aad11bb3d855'; //验证参数


    //铭科支付
    public function mkPay($params)
    {
        try {
            if ($params['orderNo'] == '') {
                return $this->failure(1, '没有订单号,请稍后重试！', $params);
            }
            if ($params['money'] == '') {
                return $this->failure(1, '没有支付金额,请稍后重试！', $params);
            }
            $parameters = $this->payParameters($params);  //获取支付参数
            $parameters['sign'] = md5($parameters['signSource']);
            unset($parameters['signSource']);
            $url = $this->wgurl . '/api/createOrder'; //网关地址
            $response = $this->post($url, $parameters);
            if ($response) {
                $re = json_decode($response, true);
                return ('alipays://platformapi/startapp?appId=20000067&url=' . utf8_encode($re['obj']));
            }
            return $this->validation('恒云返回支付异常', $response);
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
    /**
     *  提取支付参数
     * @return array
     */
    public function payParameters($params)
    {
        try {
            // 支付参数
            $orderNo = $params['orderNo'];    //订单号，唯一
            $appid = $this->appid;    //	Appid
            $key = $this->key;
            // 在金额后面随机两位数
            $doc = rand(1,10) / 100;
            $money = $params['money'] + $doc;    //	订单金额
            $returnUrl = '';
            if ($params['notifyUrl']) {
                $notifyUrl = $params['notifyUrl'];    //	通知地址
            } else {
                $notifyUrl = "http://120.24.250.152/common/mkpayback";    //	通知地址
            }
            $productName = $params['productName'];    //	商品描述
            $signSource = sprintf("appId=$appid&money=$money&notifyUrl=$notifyUrl&orderNo=$orderNo&productName=$productName&key=$key");
            $native = array(
                "appId" => $appid,
                "money" => $money,
                "returnUrl" => $returnUrl,
                "notifyUrl" => $notifyUrl,
                "orderNo" => $orderNo,
                "productName" => $productName,
                "signSource" => $signSource,
            );
            return $native;
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }
}
