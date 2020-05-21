<?php

namespace App\Services;

use App\Facades\JhPayFacade;
use App\Models\FinanceRecharge;
use App\Models\PlatformPayment;
use app\Sdk\Alipay\Alipay;
use App\Traits\ResultTrait;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * 支付宝
 * @package App\Http\Service
 */
class AlipayService
{
    use ResultTrait;

    private $appId = 2021001106655588;
    private $publicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAj/H2DcZpPYYn1jXfdoDO+MsiCoUxjBYOJ+0nBwvGrgQpUs5yBrlWgcUhl9kQntwxe5bSnvidgvO/VoBXpvfN+b9SSFkOgHazZA1aSeCndRyYQIJZ9OBqPFpWXwZ+uNuUTOR2dn/rql5q8HnLebjldRyM/ZjTKqWNAoXOPoMRMANw83Ch4o6L3gCOO09hj1ZcN7jPEvdK+OzPTLwPJ39HN+BII3Xa9Wnde7+YnmsTqGvdcyu6CTOYeqXfYlJ86i/y6cg02Uof9Fd7OGgFwb6cUeHTLb0AEZavHE1adSUwjYXviCXa6V4Ha86wMwbcmFlEn2Om8SqLlgoa64vayqJ89QIDAQAB';
    private $privateKey = 'MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC7kXmLZQ/SVKBYbjERnZtWXN87PuEkHYQEagxDuHNtDlKpou/581QSvO0Xc9W+jSXxmR59c0f0wqWcNyMTTXacH8NvwGCUHfGxKLyFdauDp8VI0yQCaqAINlIkBV9GWbkP3w+3cvsu/7IpsJiKuJqUV+bvw4X1LbBY6M+H5uK62w71c2qQ+0QyLHiq2y8MuJHjFbyOGH7O8PLupqKk79l0pqo+kJ+tTaX3VNgX73YkQkHugkt4SXJU0npqA+0dJVn25nOf9e8jYsm5QFlok89eLzSu8JBv6a7ZeZBNnywaZDTTEDb+d0EZcyC66hq8BbohjtsW6tUm6nOfmMwS8BO5AgMBAAECggEAFDzC725NAbWNw1zIQI1PuEa28y56nintF4jVn+zjz01vT8w7ti0x0f++WPxvgGq8QE/0ATcR+W+S7MLqLuf5tyiaLpGEGhcM/79Ub9XdVowgdhYJ2ZTlhV1PCYHm4LFHjVXU3UczoMOH1oWuP7FhREYjrZTb/DMcxsr/sTsRFXXjWIxN1L1Lw3X3fWBnFMmwRgUqNjfVPmSstfM8u7hybw/OAGimuFeOi7jmtiWnHI+jKhQpIZy1xG4iJR1FYTO54dM8ShrIW5EFcoq4xVaFvvQddfN54Yi3sSiwVkZrF++l8FJFAPL1NFxpHexJsroXHI35XxFgXv9bFbMSWKcjgQKBgQD+49ERVNWGd45bRQ5osEasevClPMhA8PpottQrMbNYcagZeLI54r/ZQdyY8f9Sgh5J6WRmHmnS+LimZhbBigB/yfV7PfY+My+jQnGEVja4sEMqVu7cz/rgOYKpmtZJtPx4qkrIp0mt3pSPr5nAtpan20pnG697sEt5BjpsbBdBaQKBgQC8Ypl2/kUTZZ6yJfnL23Um+7KIVVSbDGp/jShINIYsh2JzXgww+DKIaui3q5c/nIm2baBnjLXBtmXWEewje+7nqOZ3hQq3eoW7R3ufqxk9JbDLmXdV0bppVHcCim0FW0cCy3clTwiRoBw9wy4wRvid9pQtaveYcfXm+XLvnBOl0QKBgQDPY/YcL/Z8rpJ52Lpcm/ElLfLXv5kAnhJhWUFQ0+OlNvUbpIT/bGvFDj27hEvGinCymuBB5sUcD5vOaIpjdzHoyB6g5E2TVnqQT+OlVbC4nVJJBOrdZ6ws/R4/eeiZmFVEqOqoUNU2T0B5mNt1Xhs/AMKNGo2yCkOeeV7YESrq4QKBgHS0/PAsHG0k4ojpX/S8U7q/6d3uR0j7CytULuV0rL7/bK2eUR0xVlUPLndDNaNx/hrnlZ0xHhURQ8u4NLvS9rHMAGOBJ599p0Xbximn3S31oK6xt62SDdNdQZFwYpQT9U008e5KJTvankRhG8dK4JE0Bp6Qiy2FRFtApMTuw3lRAoGADQjM4Kb4crVC7greMAdGtOQ0ackfxRRYCIwV5MPAGygaMW/xV7TKA+A1LcMH84cjse7HtanMUpOmyt875BelFWFb7tGGEsvfFCny5sSedVCO0gnCT7YD6pfgppBmXz1HWQR4Mxi98E/YTOzz7oZ7RjKCnypxhZvGoRYxiwVE7c0=';
    private $client;
    private $alipayService;

    public function __construct($appId = '', $publicKey = '', $privateKey = '')
    {
        $this->client = new Client([
            'timeout' => 5.0,
        ]);
        if (!empty($appId)) {
            $this->appId = $appId;
        } else {
            $this->appId = env('ALIPAY_APPID');
        }
        if (!empty($publicKey)) {
            $this->publicKey = $publicKey;
        } else {
            $this->publicKey = env('ALIPAY_PUBLICKEY');
        }
        if (!empty($privateKey)) {
            $this->privateKey = $privateKey;
        } else {
            $this->privateKey = env('ALIPAY_PRIVATEKEY');
        }
    }

    /**
     * 收款
     * @return mixed
     */
    public function Receipt(FinanceRecharge $financeRecharge)
    {
        try {
            // 调支付
            $this->alipayService = new Alipay($this->appId, $this->publicKey, $this->privateKey);
            $resp = $this->alipayService->pay($financeRecharge);
            if ($resp) {
                return $this->succeed($resp);
            }
            return $this->failure(1, '支付请求失败', $financeRecharge);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     *  收款订单查询
     * @param $out_trade_no
     * @param null $trade_no
     * @return array
     */
    public function ReceiptQuery($out_trade_no, $trade_no = null)
    {
        try {
            $this->alipayService = new Alipay($this->appId, $this->publicKey, $this->privateKey);
            $result = $this->alipayService->query($out_trade_no, $trade_no);
            if (!$result){
                return $this->validation('查询失败或交易不存在');
            }
            $params = (array)$result->alipay_trade_query_response;
            if ($params['code'] != '10000') {
                return $this->validation('查询成功,' . $params['sub_msg']);
            }
            //交易成功
            if ($params['trade_status'] == 'TRADE_SUCCESS') {
                $data['no'] = $params['trade_no']; // 支付宝订单号
                $data['out_order'] = $params['out_trade_no']; // 充值原始订单号
                $data['money'] = $params['total_amount']; // 充值金额
                $data['received_money'] = $params['receipt_amount']; // 实到金额
                $data['timestamp'] = strtotime(Carbon::now()->toDateTimeString());
                return JhPayFacade::callBack($data);
            }

            //付款中
            if ($params['trade_status'] == 'WAIT_BUYER_PAY') {
                return $this->succeed(null, '查询成功,交易创建，等待买家付款');
            }

            //交易关闭
            if ($params['trade_status'] == 'TRADE_CLOSED') {
                return $this->succeed(null, '查询成功,未付款交易超时关闭，或支付完成后全额退款');
            }

            //交易结束，不可退款
            if ($params['trade_status'] == 'TRADE_FINISHED') {
                Log::info('支付宝查询', $params);
                return $this->succeed(null, '查询成功,交易结束，不可退款');
            }
            return $this->succeed(null, '查询成功');
        } catch (\Exception $ex) {
            $this->exception($ex);
            return $this->validation('支付宝查询异常，请联系管理员');
        }
    }
}
