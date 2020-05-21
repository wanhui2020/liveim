<?php


namespace App\Gateways;


use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Gateways\Gateway;
use Overtrue\EasySms\Support\Config;
use Overtrue\EasySms\Traits\HasHttpRequest;

class AliyunGateway extends Gateway
{
    use HasHttpRequest;

    const ENDPOINT_HOST = 'http://api.chanzor.com';

    const ENDPOINT_URI = '/send';

    protected $account;

    protected $password;

    protected $sign = null;

    protected $client;

    /**
     * Send a short message.
     *
     * @param \Overtrue\EasySms\Contracts\PhoneNumberInterface $to
     * @param \Overtrue\EasySms\Contracts\MessageInterface $message
     * @param \Overtrue\EasySms\Support\Config $config
     *
     * @return array
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        $params = [
            'account' => $config->get('account'),
            'password' => $config->get('password'),
            'content' => $message->getContent() . '【' . $config->get('sign') . '】',
            'mobile' => $to->getNumber(),
        ];

        return $this->post(self::ENDPOINT_URI, $params);
    }

    protected function getBaseUri()
    {
        return self::ENDPOINT_HOST;
    }
}