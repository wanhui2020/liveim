<?php

namespace App\Messages\Sms;

use Overtrue\EasySms\Message;
use Overtrue\EasySms\Contracts\GatewayInterface;
use Overtrue\EasySms\Strategies\OrderStrategy;

class CodeMessage extends Message
{
    public $code;
    protected $strategy = OrderStrategy::class;           // 定义本短信的网关使用策略，覆盖全局配置中的 `default.strategy`
    protected $gateways = ['qcloud', 'aliyun',]; // 定义本短信的适用平台，覆盖全局配置中的 `default.gateways`

    public function __construct()
    {
        parent::__construct();
        $this->code = rand('1000', '9999');
    }

    // 定义直接使用内容发送平台的内容
    public function getContent(GatewayInterface $gateway = null)
    {
        return sprintf('尊敬的用户，您的验证码为%d，有效期为一分钟，如非本人操作，请忽略本条消息。', $this->code);
    }

    // 定义使用模板发送方式平台所需要的模板 ID
    public function getTemplate(GatewayInterface $gateway = null)
    {
        if ($gateway->getName() == 'qcloud') {
            return '212295';
        }

        return 'SMS_145598227';
    }

    // 模板参数
    public function getData(GatewayInterface $gateway = null)
    {
        if ($gateway->getName() == 'qcloud') {
            return [
                '1' => $this->code
            ];
        }
        return [
            'code' => $this->code,
        ];
    }
}