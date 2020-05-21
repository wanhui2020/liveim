<?php

namespace app\Sdk\Alipay;

use AlipayFundTransToaccountTransferRequest;
use AlipayOpenPublicTemplateMessageIndustryModifyRequest;
use App\Models\FinanceRecharge;
use App\Models\OrderAcquiring;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

require('AopSdk.php');

class Alipay
{

    private $config = [
        'gatewayUrl' => 'https://openapi.alipay.com/gateway.do',
        'charset' => 'UTF-2',
        'sign_type' => 'RSA2',
    ];

    public function __construct($app_id, $public_key, $private_key)
    {
        $this->config['app_id'] = $app_id;
        $this->config['public_key'] = $public_key;
        $this->config['private_key'] = $private_key;
        $this->config['notify_url'] = env('APP_URL') . '/callback/pay/alipay/notify';
        $this->config['return_url'] = env('APP_URL') . '/pay/alipay/succeed';
    }


    /**
     * H5支付
     * @param string $orderId 商品订单号
     * @param string $subject 支付商品的标题
     * @param string $body 支付商品描述
     * @param float $total_amount 商品总支付金额
     * @param int $expire 支付过期时间，分
     * @return bool|string  返回支付宝签名后订单信息，否则返回false
     */
    public function pay(FinanceRecharge $financeRecharge)
    {
        try {
            $orderId = $financeRecharge->no;
            $subject = '订单：' . $financeRecharge->no;
            $body = '订单号：' . $financeRecharge->no;
            $total_amount = round($financeRecharge->money, 2);
            $expire = 10;

            $aop = new \AopClient();
            $aop->gatewayUrl = $this->config['gatewayUrl'];
            $aop->appId = $this->config['app_id'];
            $aop->rsaPrivateKey = $this->config['private_key'];
            $aop->alipayrsaPublicKey = $this->config['public_key'];
            $aop->format = 'json';//固定
            $aop->charset = $this->config['charset'];
            $aop->signType = $this->config['sign_type'];
            $params = new \stdClass();
            if ($this->isMobile()) {
                $request = new \AlipayTradeWapPayRequest();
                $params->product_code = 'QUICK_MSECURITY_PAY';
                $params->timeout_express = $expire;
            } else {
                $request = new \AlipayTradePagePayRequest ();
                $params->timeout_express = $expire . 'm';
                $params->product_code = 'FAST_INSTANT_TRADE_PAY';
            }
            $params->body = $body;
            $params->subject = $subject;
            $params->out_trade_no = $orderId;
            $params->total_amount = $total_amount;
            $request->setNotifyUrl(url('/common/alipayback?no=') . $orderId);
            if ($this->isMobile()) {
                $request->setReturnUrl(env('APP_URL_H5') . '/#/my');
            } else {
                $request->setReturnUrl(env('APP_URL_PC') . '/#/my');
            }
            $request->setBizContent(json_encode($params));
            $response = $aop->pageExecute($request);
            return $response;
        } catch (Exception $e) {
            Log::error('异常', [$e->getMessage()]);
            return false;
        }
    }

    private function isMobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) {
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高。其中'MicroMessenger'是电脑微信
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile', 'MicroMessenger');
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }

    /**
     * 订单取消
     * @param $out_order
     */
    public function cancel($out_trade_no, $trade_no = null)
    {
        try {
            $aop = new \AopClient();
            $aop->gatewayUrl = $this->config['gatewayUrl'];
            $aop->appId = $this->config['app_id'];
            $aop->rsaPrivateKey = $this->config['private_key'];
            $aop->alipayrsaPublicKey = $this->config['public_key'];
            $aop->format = 'json';//固定
            $aop->charset = $this->config['charset'];
            $aop->signType = $this->config['sign_type'];
            $request = new \AlipayTradeCancelRequest ();
            $params = new \stdClass();
            $params->out_trade_no = $out_trade_no;
            $params->trade_no = $trade_no;
            $request->setBizContent(json_encode($params));
            $result = $aop->execute($request);
            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;
            if (!empty($resultCode) && $resultCode == 10000) {
                return $result->$responseNode;
            } else {
                return false;
            }


        } catch (Exception $e) {
            //失败返回false
            return false;
        }
    }

    /**
     * 订单查询
     * @param $out_order
     * @return bool
     */
    public function query($out_trade_no, $trade_no = null)
    {
        try {
            $aop = new \AopClient();
            $aop->gatewayUrl = $this->config['gatewayUrl'];
            $aop->appId = $this->config['app_id'];
            $aop->rsaPrivateKey = $this->config['private_key'];
            $aop->alipayrsaPublicKey = $this->config['public_key'];
            $aop->format = 'json';//固定
            $aop->charset = $this->config['charset'];
            $aop->signType = $this->config['sign_type'];
            $request = new \AlipayTradeQueryRequest();
            $params = new \stdClass();
            $params->out_trade_no = $out_trade_no;
            $params->trade_no = $trade_no;

            $bizcontent = json_encode($params);

            $request->setBizContent($bizcontent);
            $response = $aop->execute($request);

            return $response;

        } catch (Exception $e) {
            Log::error('支付查询异常', [$e->getMessage()]);
            //失败返回false
            return false;
        }
    }


    public function verify($params)
    {
        try {
            $aop = new \AopClient();
            $aop->gatewayUrl = $this->config['gatewayUrl'];
            $aop->appId = $this->config['app_id'];
            $aop->rsaPrivateKey = $this->config['private_key'];
            $aop->alipayrsaPublicKey = $this->config['public_key'];
            $aop->format = 'json';//固定
            $aop->charset = $this->config['charset'];
            $aop->signType = $this->config['sign_type'];
            return $aop->rsaCheckV1($params, null, $aop->signType);

        } catch (Exception $e) {
            //失败返回false
            return false;
        }
    }

    /**
     * 代付
     * @param $out_biz_no
     * @param $payee_account
     * @param $amount
     * @param string $payee_type
     * @return bool|mixed|\SimpleXMLElement|string
     */
    public function transfer($out_biz_no, $payee_account, $amount, $payee_type = 'ALIPAY_LOGONID')
    {
        try {
            $aop = new \AopClient();


            $aop->gatewayUrl = $this->config['gatewayUrl'];
            $aop->appId = $this->config['app_id'];
            $aop->rsaPrivateKey = $this->config['private_key'];
            $aop->alipayrsaPublicKey = $this->config['public_key'];
            $aop->format = 'json';//固定
            $aop->charset = $this->config['charset'];
            $aop->signType = $this->config['sign_type'];

            $request = new AlipayFundTransToaccountTransferRequest();
//SDK已经封装掉了公共参数，这里只需要传入业务参数
//此次只是参数展示，未进行字符串转义，实际情况下请转义
            $params = new \stdClass();
            $params->out_biz_no = 'XXXX';//商户生成订单号
            $params->payee_type = 'ALIPAY_LOGONID';//收款方支付宝账号类型
            $params->payee_account = 'XXXXX';//收款方账号
            $params->amount = '1';//总金额
//            $params->payer_show_name = '';//付款方账户
//            $params->payee_real_name = '';//收款方姓名
//            $params->remark = '';//转账备注

            $request->setBizContent(json_encode($params));
            $response = $aop->execute($request);
            return $response;
            return htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。

        } catch (Exception $e) {
            //失败返回false
            return false;
        }
    }
}
