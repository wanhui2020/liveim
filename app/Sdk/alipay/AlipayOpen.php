<?php

namespace app\Sdk\Alipay;

use AlipayFundTransToaccountTransferRequest;
use AlipayOpenPublicTemplateMessageIndustryModifyRequest;

use Exception;
use Illuminate\Http\Request;

require('AopSdk.php');

class AlipayOpen
{

    private $config = [
        'gatewayUrl' => 'https://openapi.alipay.com/gateway.do',
        'app_id' => '2018021202184674',
        'charset' => 'UTF-2',
        'sign_type' => 'RSA2',
    ];

    public function __construct()
    {
        $this->config['app_id'] = env('ALIPAY_APPID');
        $this->config['public_key'] = env('ALIPAY_PUBLIC_KEY');
        $this->config['private_key'] = env('ALIPAY_PRIVATE_KEY');
        $this->config['notify_url'] = env('APP_URL') . '/callback/pay/alipay/notify';
        $this->config['return_url'] = env('APP_URL') . '/pay/alipay/succeed';
    }

    public function init()
    {
        $aop = new \AopClient();
        $aop->gatewayUrl = $this->config['gatewayUrl'];
        $aop->appId = $this->config['app_id'];
        $aop->rsaPrivateKey = $this->config['private_key'];
        $aop->alipayrsaPublicKey = $this->config['public_key'];
        $aop->format = 'json';
        $aop->charset = $this->config['charset'];
        $aop->signType = $this->config['sign_type'];
        return $aop;
    }

    /**
     * 获取授权
     * @param $app_auth_code
     * @return mixed|\SimpleXMLElement|string
     */
    public function authToken($app_auth_code)
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
            $request = new \AlipayOpenAuthTokenAppRequest();
            //SDK已经封装掉了公共参数，这里只需要传入业务参数
            $params = new \stdClass();
            $params->grant_type = 'authorization_code';
            $params->code = $app_auth_code;

            $bizcontent = json_encode($params);
            $request->setBizContent($bizcontent);

            $response = $aop->execute($request);
            return $response;
        } catch (Exception $e) {
            //失败返回false
            return false;
        }
    }

    public function agentCreate($account, $name, $mobile, $email)
    {
        try {
            $request = new \AlipayOpenAgentCreateRequest ();
            $params = [
                'account' => $account,
                'contact_info' => [
                    'contact_name' => $name,
                    'contact_mobile' => $mobile,
                    'contact_email' => $email,
                ],];

            $bizcontent = json_encode($params);
            $request->setBizContent($bizcontent);

            $result = $this->init()->execute($request);
            Result::logs('agentCreate', $result);
            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;

            if (!empty($resultCode) && $resultCode == 10000) {
                return $result->$responseNode;
            }
            return false;

        } catch (Exception $e) {
            //失败返回false
            return false;
        }
    }

}
