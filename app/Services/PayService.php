<?php

namespace App\Services;

use App\Traits\ResultTrait;
use Illuminate\Http\Request;
use Yansongda\Pay\Pay;

//支付服务
class PayService
{
    use ResultTrait;
    private $config;


    public function __construct($gateway = 'app')
    {

        $this->config = [
            'alipay' => [
                'app_id' => '2021001106655588',             // 支付宝提供的 APP_ID 2088731281850616
                'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAj/H2DcZpPYYn1jXfdoDO+MsiCoUxjBYOJ+0nBwvGrgQpUs5yBrlWgcUhl9kQntwxe5bSnvidgvO/VoBXpvfN+b9SSFkOgHazZA1aSeCndRyYQIJZ9OBqPFpWXwZ+uNuUTOR2dn/rql5q8HnLebjldRyM/ZjTKqWNAoXOPoMRMANw83Ch4o6L3gCOO09hj1ZcN7jPEvdK+OzPTLwPJ39HN+BII3Xa9Wnde7+YnmsTqGvdcyu6CTOYeqXfYlJ86i/y6cg02Uof9Fd7OGgFwb6cUeHTLb0AEZavHE1adSUwjYXviCXa6V4Ha86wMwbcmFlEn2Om8SqLlgoa64vayqJ89QIDAQAB',     // 支付宝公钥，1行填写
                'private_key' => 'MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCvrIOv11WEan0oQ7gJWGhtlcD8FIX8ogV9gDg4JQ/wQ7IYKBp6f8wjlnlx2qmiUdiDUiaB5e/8ecoLvIfncIPvAXeCGr2s7jWRJsjNzUwrOZNuYOMGd9uaTdRmQ+ImBoOc/sx6imHWaJg5+k1pbSCQDkklcAZgWK3vU1xBya+uxVJQPVNLxWZjLoyM0WuNfCKhCWM2y1KeS07z1YebNK7NU6U9+SkXO/VP9H3kxtz6xU6k2XxlKZHzitax7knW1vkHu7hZS6+CX5Gifv6eSGfw8lCYj/u4zJ7Pa9bGBW08f7MmyHCK3KZLRQalxPu5IT6ifyt+OaH5dlvuJq1/7VF7AgMBAAECggEAVkMbAtx1JKl5vazLEOO1m6H+eonFZVFV6tNsR72DPxKuwAfpQJ/CaPM5vTuHMqqH66wKNpznJA3Vtb+k6HOqhVEuEtf4rZLnANiPn+cgcNU73OZ8dC+kyPdQxcqtoMhwFkZTuBR9iGAh585N9ufcJoCtUFHa/7Jr7mn7kl5sAyAWLDusZxBfV8vgnWUFVa71MC9m5CMC+dwLpeValSWvsj4SniBZLi6xs303Vbyoj4cWxqjC41eWaESnQ5G75Xa74LN8P3Xchnk1wj7jumTl0tt76SIRRV0xlgQDFVcE4yDNvzo095FrX7jU1Mt/Hs/2kFZGD4O7BvshZtE6+btqoQKBgQDrbVz4vJiM6Zvk2TN+Yha90Qr3tVuwXN1KMcxgb/ht5XApY7PsRudCTLmzVp8KdwhNNP0rZMYHX3iuPvYfovsO7DfiOT4cbGOHwaPEskXZShPvVg8qQNr9OplbFnk+zACsBksoTl4QOKTHVmR0lIIiQCgMYzEn1Ie7QtxrjCAKiwKBgQC/Bm+81mXsZgSJIN2qAfzp7i2qAwIJbNDxZ2V7oZJmR2wOciPEtn52xp8CgNKxhhJoeunuV70sHzAEUNGONhjrL5cs6ixD2/yX9YSvGMy1RHJoks8j1tPVBlHHQWbazquFa5nUxW3JJIwFcsYzlMtckA8cEYdOskJuOAhufiLi0QKBgARA1Y0o2xiwn8BirCt8WizTuf7/p8hn5ReSDr8vRq21l3En+/goz8TC3hf/WKA3xk6exnQiPfGkJ+n9+TRZHXAHlHrhzd11l9a8CNlk3x4t2G5af6ujwFES3fJnVYls5hY1huYThF+GpnNzfB0fEbrMreyLXjf5/vnDG3hV775BAoGAS5azbmj0SfgQgWa08An7V2H9RdIM40fg4jWE7cgAk2JdutWlm7iPEFcGIspxFPg1noxMtxiW7belm3+TfI+hiqb5TjeXrn6FVGg9yb+peW0NTJ4TQ15F0ny+rXcOyTSQoKn0ZEJ/b+F7WsiQ72ZIlmcW44d4IEbfjJe1lmtpSJECgYAOBGA8Nny5RKvrxvps21iax8dkYCeCeMs0yAlh5/EsvKtoWW/SCJ3MqZJzBqxT3yUYa94DrG7dyNGR4BY8MDQNgLA3haXwcFm69mm/CD34stoBTlXU5dkN9yh4Pq5JW8kUTs++fmcCfCixBks7E/ZKITvoLeKHKWaeFsznIoSDIA==',        // 自己的私钥，1行填写
                'return_url' => url('callback/pay/alipayCallback'),         // 同步通知 url，*强烈建议加上本参数*
                'notify_url' => url('callback/pay/alipayCallback'),         // 异步通知 url，*强烈建议加上本参数*
            ],
            'wechat' => [
                'app_id' => 'wx9481d219a0f48687', //wx9481d219a0f48687
                'mch_id' => '1569471791', //1569471791
                'key' =>"zhaocheng11637501641762365756888",
                'notify_url' => url('callback/pay/wechatCallback'),
//                'mode' => 'dev',
            ],
        ];

    }

    /**
     *   支付宝支付
     */
    public function alipay($orderNo, $money, $title = '预定商品')
    {
        try {
            $config_biz = [
                'out_trade_no' => $orderNo,         // 订单号
                'total_amount' => $money,         // 订单金额，单位：元
                'subject' => $title ?? '预定商品',   // 订单商品标题
            ];
            $alipay = Pay::alipay($this->config['alipay'])->wap($config_biz);
            return $alipay->send();
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * 微信H5支付
     * @param $orderNo
     * @param $money
     * @param string $title
     * @return array|\Symfony\Component\HttpFoundation\Response
     */
    public function wechatH5($orderNo, $money, $title = '预定商品')
    {
        try {
            $config_biz = [
                'out_trade_no' => $orderNo,         // 订单号
                'total_fee' => (int)$money,         // 订单金额，单位：分
                'body' => $title ?? '预定商品',   // 订单商品标题
//                'openid' => 'oULq6v4a3aPsaokoiLh2XQjsEWB8',
            ];
//            dd($this->config['wechat'],$config_biz);
            $alipay = Pay::wechat($this->config['wechat'])->wap($config_biz);
            return $alipay->send();
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }
    public function notify()
    {
        $pay = Pay::wechat($this->config['wechat']);
        try{
            $data = $pay->verify(); // 是的，验签就这么简单！
            Log::info('Wechat notify', $data->all());
        } catch (\Exception $e) {
        }
        return $pay->success();// laravel 框架中请直接 `return $pay->success()`
    }
    /**
     *   支付宝订单查询
     */
    public function alipayQuery($orderNo)
    {
        try {
            $config_biz = [
                'out_trade_no' => $orderNo,         // 订单号
            ];
            $alipay = Pay::alipay($this->config['alipay'])->find($config_biz);
            return $this->succeed($alipay, '生成支付成功');
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    public function alipayNotify(Request $request)
    {
        $alipay = Pay::alipay($this->config['alipay']);
        try {
            $verify = $alipay->verify();
            $data = $verify->all();
            if ($data['trade_status'] == 'TRADE_SUCCESS' || $data['trade_status'] == 'TRADE_FINISHED') {
                return $this->succeed($data, '付款成功');
            }
            return $this->failure(1, '付款失败', $data);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /*
     * 三方聚合支付(通易支付)
     * */
    public function typay($no, $amount, $payuser, $notifyMethod = '')
    {
        try {
            $code = 'test'; //商户号
            $key = '7a34d2e11f699a3ca57529cc89ace396'; //密钥
            $param = 'no=' . $no . '&payuser=' . $payuser . '&amount=' . $amount . '&code=' . $code;
            $sign = md5($param . $key);
            $bindUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/callback/pay/typay'; //
            if ($notifyMethod != '') {
                $bindUrl = $bindUrl . '/' . $notifyMethod;
            }
            $payParam = 'http://pay.hjhp.cn/pay/api/grcode?' . $param . '&tzurl=' . $bindUrl . '&sign=' . $sign;
            $retJson = json_decode(file_get_contents($payParam), true);
            if ($retJson['status'] == 0) {
                $payParam = $retJson['data']['url'];
            }
            return $payParam;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /*
     * 拼多多三方支付
     *  type:wechat/alipay
     * */
    public function pddPay($no, $amount, $type = 'wechat', $notifyMethod = 'recharge')
    {
        try {
            list($t1, $t2) = explode(' ', microtime());
            $getMillisecond = (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);

            $client_id = '0eb2d4edd86672c06b82d56d94126384'; //商户号
            $signkey = '9665848c3444bb28b1e7d740fa1f7aa3846e129f5746a832d9ceef14a67190dd';
            $url = 'http://api.yingshiguoji.hk/index/api/order';

            $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/callback/pay/pddpay/' . $notifyMethod; //

            $data = array(
                'type' => $type, // 通道代码 alipay/wechat
                'total' => $amount, // 金额
                'notify_url' => $notify_url, // 异步回调地址
                'client_id' => $client_id,  //  商户ID
                'timestamp' => $getMillisecond, // 获取13位时间戳
                'api_order_sn' => $no
            );
            unset($data['sign']);
            ksort($data);
            $str = '';
            foreach ($data as $k => $v) {
                $str = $str . $k . $v;
            }
            $str = $signkey . $str . $signkey;
            $data['sign'] = strtoupper(md5($str));
            //
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $ret = curl_exec($ch);
            curl_close($ch);
            $res = json_decode($ret, true);
            return $res;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }
}
