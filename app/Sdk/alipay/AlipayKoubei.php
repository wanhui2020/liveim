<?php

namespace app\Sdk\Alipay;

use AlipayFundTransToaccountTransferRequest;
use AlipayOpenPublicTemplateMessageIndustryModifyRequest;
use Exception;
use Illuminate\Http\Request;

require('AopSdk.php');

class AlipayKoubei
{

    private $config = [
        'gatewayUrl' => 'https://openapi.alipay.com/gateway.do',
        'app_id' => '2018021202184674',
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
    public function pay($orderId, $subject, $body, $total_amount, $expire)
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
            $request = new \AlipayTradeWapPayRequest();
            //SDK已经封装掉了公共参数，这里只需要传入业务参数
            $params = new \stdClass();
            $params->body = $body;
            $params->subject = $subject;
            $params->out_trade_no = $orderId;
            $params->timeout_express = $expire;
            $params->total_amount = $total_amount;
            $params->product_code = 'QUICK_MSECURITY_PAY';

            $bizcontent = json_encode($params);
            $request->setNotifyUrl($this->config['notify_url']);
            $request->setReturnUrl($this->config['return_url']);
            $request->setBizContent($bizcontent);

            $response = $aop->sdkExecute($request);
//            return $response;
            //是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
            return htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。
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
            $params->out_biz_no = 'XXX';//商户生成订单号
            $params->payee_type = 'ALIPAY_LOGONID';//收款方支付宝账号类型
            $params->payee_account = 'XXXX';//收款方账号
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
