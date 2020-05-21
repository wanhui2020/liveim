<?php

namespace App\Services;

use App\Models\MemberInfo;
use App\Traits\ResultTrait;
use Tencent\TLSSigAPI;

//推送服务
class PushService
{
    use ResultTrait;
    private $config;
    private $client;

    public function __construct()
    {
        $this->config = [
            'base_url' => 'https://openapi.xg.qq.com/v3/push/app',
            'app_id' => '96aa30c3de443',
            'app_key' => '097d25d236fa86e98dbad3c3b42a37b9',
        ];

    }

    /*
     * 推送
     * audience_type = all：全量推送
     * audience_type = tag：标签推送
     * audience_type = token：单设备推送
     * audience_type = token_list：设备列表推送
     * audience_type = account：单账号推送
     * audience_type = account_list：账号列表推送
     * platform = android 安卓
     * platform = ios 苹果
     * message_type = notify 通知消息
     * message_type = message 透传消息/静默消息
     * */
    public function pushMessage($audience_type = 'all', $platform = 'android', $message_type = 'notify', $token_list = array(), $message = array())
    {
        $data = array(
            'audience_type' => $audience_type,
            'platform' => $platform,
            'message_type' => $message_type,
            'message' => $message,
        );
        if ($audience_type == 'token' || $audience_type == 'token_list') {
            $data['token_list'] = $token_list;
        }
        if ($audience_type == 'token_list') {
            $data['push_id'] = 0;
        }
        if ($platform == 'ios') {
            $data['environment'] = 'dev';
        }
        return $this->requestPost($data);
    }

    /*
     * 安卓普通消息
     * */
    public function androidMessage($title, $content, $token_list = array(), $message_type = 'notify')
    {
        $data = array(
            'title' => $title,
            'content' => $content,
            'android' => array(
                'n_id' => 0,
                'builder_id' => 0,
                'ring' => 1,
            )
        );
        $audience_type = 'all';
        if (count($token_list) > 0) {
            $audience_type = 'token';
        }
        return $this->pushMessage($audience_type, 'android', $message_type, $token_list, $data);
    }

    /*
     * IOS普通消息
     * */
    public function iosGeneral($title, $content, $token_list = array())
    {
        $data = array(
            'title' => $title,
            'content' => $content,
            'ios' => array(
                'aps' => array(
                    'alert' => array(
                        'subtitle' => $title
                    ),
                    'badge_type' => 5,
                    'category' => 'INVITE_CATEGORY'
                )
            )
        );
        $this->config['app_id'] = 'ec668dce3dcc3';
        $this->config['app_key'] = '7f59c292d3e9b233acaf94c4a7f6cf90';
        $audience_type = 'all';
        if (count($token_list) > 0) {
            $audience_type = 'token';
        }
        return $this->pushMessage($audience_type, 'ios', 'notify', $token_list, $data);
    }


    //HTTP请求（支持HTTP/HTTPS，支持GET/POST）
    function requestPost($data = null)
    {
        $base64_auth_str = base64_encode($this->config['app_id'] . ':' . $this->config['app_key']);
        $url = $this->config['base_url'];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization:Basic ' . $base64_auth_str, 'Content-Type:application/json']);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            if (is_array($data)) {
                $data = json_encode($data);
            }
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        curl_close($curl);
        $output = json_decode($output, true);
        return $output;
    }


    /*
     * 推送消息给指定会员
     * */
    public function pushToMember($title, $content, $memberid)
    {
        $memberInfo = MemberInfo::find($memberid);
        if (isset($memberInfo) && !empty($memberInfo->push_token)) {
            $platform = $memberInfo->platform; //会员所用平台
            $token = [$memberInfo->push_token];
            if ($platform == 'ios') {
                $data = $this->iosGeneral($title, $content, $token);
            } else {
                $data = $this->androidMessage($title, $content, $token);
            }
            $this->logs('推送日志', $content);
        }
    }

}
