<?php

namespace App\Services;

use App\Traits\ResultTrait;

//汇潮支付
class HcPayService
{
    use ResultTrait;
    private $website;//授权域名
    private $wgurl = 'https://pay.iisck.cn'; //网关url
    private $pay_memberid = '10122'; //商户号
    private $mch_id = '98831515'; //商户号
    private $pay_type = '205'; //通道类型
    private $key = 'eld8v5mk05s4u0b1m80wxrpzekevumpy'; //验证参数


    //汇潮支付
    public function hcPay($params)
    {
        try {
            if ($params['out_trade_no'] == '') {
                return $this->failure(1, '没有订单号,请稍后重试！', $params);
            }
            if ($params['amount'] == '') {
                return $this->failure(1, '没有支付金额,请稍后重试！', $params);
            }
            $parameters = $this->payParameters($params);  //获取支付参数
            $parameters['sign'] = md5($parameters['signSource']);
            unset($parameters['signSource']);
            $url = $this->wgurl . '/gateway/pay'; //网关地址
            $response = $this->post($url, $parameters);
            if ($response) {
                $re = json_decode($response, true);
                return ('alipays://platformapi/startapp?appId=20000067&url=' . utf8_encode($re['code_url']));
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
            $version = "1.0";    //  版本号
            if ($params['way'] == 10) {  //恒云支付1
                $mch_id = '98831515';    //	商户号 98831515
                $key = 'eld8v5mk05s4u0b1m80wxrpzekevumpy'; // key eld8v5mk05s4u0b1m80wxrpzekevumpy
            } elseif ($params['way'] == 11) {//恒云支付2
                $mch_id = '98787772';    //	商户号 98787772
                $key = 'e8j1xtif4k72g2fq2ewjhi7yix9giwkg'; // key e8j1xtif4k72g2fq2ewjhi7yix9giwkg
            } elseif ($params['way'] == 12) {//恒云支付3
                $mch_id = '98458816';    //	商户号 98458816
                $key = '6hfdf369dlxa5yhc3uvzl27hc588ysdg'; // key eld8v5mk05s4u0b1m80wxrpzekevumpy
            } elseif ($params['way'] == 13) {//恒云支付4
                $mch_id = '98933292';    //	商户号 98933292
                $key = 'af84db3ztq0i4g472kjnbmyfeap133ww'; // key af84db3ztq0i4g472kjnbmyfeap133ww
            } elseif ($params['way'] == 14) {//恒云支付5
                $mch_id = '98865343';    //	商户号 98865343
                $key = 'ub7fi5675fcjzwo8vli87qbj3jvgt82a'; // key ub7fi5675fcjzwo8vli87qbj3jvgt82a
            }

            $pay_type = $this->pay_type;    //	通道类型  102支付宝扫码
            $fee_type = "CNY";    //	货币类型
            $total_amount = $params['amount'] * 100;    //	订单金额
            $out_trade_no = $params['out_trade_no'];    //订单号
            $device_info = $params['out_trade_no'];    //	设备号
            if ($params['notify_url']) {
                $notify_url = $params['notify_url'];    //	通知地址
            } else {
                $notify_url = "http://120.24.250.152/common/hypayback";    //	通知地址
            }
            $body = "充值金币";    //	商品描述
            $attach = "";    //	附加信息
            $time_start = "";    //	订单生成时间
            $time_expire = "";    //	订单失效时间
            $limit_credit_pay = "0";    //	支付方式限制
            $hb_fq_num = "";    //	花呗分期
            $hb_fq_percent = "";    //	手续费承担方
            $signSource = sprintf("version=1.0&mch_id=$mch_id&pay_type=$pay_type&total_amount=$total_amount&out_trade_no=$out_trade_no&notify_url=$notify_url&key=$key");
            $native = array(
                "version" => $version,
                "mch_id" => $mch_id,
                "pay_type" => $pay_type,
                "fee_type" => $fee_type,
                "total_amount" => $total_amount,
                "out_trade_no" => $out_trade_no,
                "device_info" => $device_info,
                "notify_url" => $notify_url,
                "body" => $body,
                "attach" => $attach,
                "time_start" => $time_start,
                "time_expire" => $time_expire,
                "limit_credit_pay" => $limit_credit_pay,
                "hb_fq_num" => $hb_fq_num,
                "hb_fq_percent" => $hb_fq_percent,
                "signSource" => $signSource,
            );
            return $native;
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }
}
