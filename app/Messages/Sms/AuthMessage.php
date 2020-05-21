<?php

namespace App\Messages\Sms;

use Overtrue\EasySms\Message;
use Overtrue\EasySms\Contracts\GatewayInterface;
use Overtrue\EasySms\Strategies\OrderStrategy;
//实名认证成功提醒
class AuthMessage extends Message
{
    public $name;
    protected $strategy = OrderStrategy::class;           // 定义本短信的网关使用策略，覆盖全局配置中的 `default.strategy`
    protected $gateways = ['qcloud', 'aliyun',]; // 定义本短信的适用平台，覆盖全局配置中的 `default.gateways`

    public function __construct($name)
    {
        parent::__construct();
        $this->name = $name;
    }

    // 定义直接使用内容发送平台的内容
    public function getContent(GatewayInterface $gateway = null)
    {
        return sprintf('尊敬的%s用户，您的实名认证信息已通过，请登录网站使用。', $this->name);
    }

    // 定义使用模板发送方式平台所需要的模板 ID
    public function getTemplate(GatewayInterface $gateway = null)
    {
        if ($gateway->getName() == 'qcloud') {
            return '293838';
        }

        //return 'SMS_145598227';
    }

    // 模板参数
    public function getData(GatewayInterface $gateway = null)
    {
        if ($gateway->getName() == 'qcloud') {
            return [
                '1' => $this->name
            ];
        }
        /*return [
            'name' => $this->name,
        ];*/
    }
}