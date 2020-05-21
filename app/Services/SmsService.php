<?php

namespace App\Services;

use App\Traits\ResultTrait;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Yuanyou\Risk\Risk;

//短信服务
class SmsService
{
    use ResultTrait;
    private $sms;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $config = [
            'url' => 'http://risk.4255.cn/',
            'token' => 'be0c5e4151465c9dcfeb0410401bc6246aac6b619dab0ceb14a1111234ce6e3f',
            'secret_key' => 'bc619a86ae754aa5bdbf33c19396fc94',
        ];
        $this->sms = new Risk();
        $this->sms->setConfig($config);
    }

    /**
     * 发送短信
     */
    public function send($mobiles, array $message)
    {
        $result = $this->sms->MessageSend($mobiles, $message['template'], $message['data']);
        return $this->succeed($result, '发送成功');
    }

    /**
     * 发送验证码
     * @param $phone
     */
    public function sendCode($phone, $minutes = 60)
    {
        if (Cache::has($phone)) {
            return $this->failure(1, '失败,请1分钟后再试', '请1分钟后再试');
        }
        $code = rand('1000', '9999');
        $message = ['template' => 33,
            'data' => ["code" => $code]];
        Cache::put($phone, $code, Carbon::now()->addSeconds($minutes));
        $result = $this->send($phone, $message);
        return $this->succeed(Carbon::now()->toDateTimeString(), '发送成功');
    }

    /**
     * 效验验证码
     * @param $phone
     * @param $code
     */
    public function verifyCode($phone, $code)
    {
        if (empty($phone) || empty($code)) {
            return false;
        }
        if (Cache::has($phone)) {
            if (Cache::get($phone) == $code) {
                Cache::forget($phone);
                return true;
            }
        }
        return false;
    }

}
