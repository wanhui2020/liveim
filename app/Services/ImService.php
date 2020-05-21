<?php

namespace App\Services;

use App\Messages\Sms\CodeMessage;
use App\Traits\ResultTrait;
use App\Utils\TencentSign;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Tencent\TLSSigAPI;

//IM服务
class ImService
{
    use ResultTrait;
    private $config;
    private $client;

    public function __construct()
    {
        $this->config = [
            'base_url' => 'https://console.tim.qq.com/v4',
            'appid' => '1400254033',
            'identifier' => 'administrator',
            'token' => 'eJxlj11PgzAYhe-5FaS3M67QFayJF44thjjIkH0oN02FsrwSPiwdbjP*dyNbMhLP7fPknJxvwzRNtFrEtyJN632luT42Epn3JsLo5gqbBjIuNCcq*wfloQEluci1VD20KKU2xkMHMllpyOFiiKyEClqthK7VQGuzgvdb554JxjadYEKGCux6GMzXnh951XS5cdzGDQvm58*0eArG7BSeEtGRl9XbZibWbO7auEzCyN9FMtevMl*myfY9CPYlm318QTxN0tiKRs4CRocu*VSPY2*bPgwmNZTycsxx6R1hDhvQTqoW6qoXbGxRyyb4L8j4MX4B3EVgDw__',
            'private_key' => base_path('ssl/im/private_key.txt'),
            'public_key' => base_path('ssl/im/public_key.txt'),
        ];

    }

    /**
     *   生成 UserSig
     */
    public function userSign($Identifier)
    {
        try {
            $sign = new TencentSign();
            $sign->setAppid($this->config['appid']);
            $sign->setPrivateKey(file_get_contents($this->config['private_key']));
            $sign->setPublicKey(file_get_contents($this->config['public_key']));
            return $sign->genSig($Identifier);
        } catch (\Exception $ex) {
            $this->exception($ex);
            return false;
        }
    }

    /**
     * 解散文章
     */
    public function DissolveRoom($roomId)
    {
        try {
            $param["Nonce"] = rand();
            $param["Timestamp"] = time();
            $param["Region"] = "ap-guangzhou";
            $param["SecretId"] = 'AKID5yb1UkOYFjyMZxkN5e1APt8DKKAnnJCq';
            $param["Action"] = "DissolveRoom";
            $param["Version"] = "2019-07-22";
            $param["SdkAppId"] = $this->config['appid'];
            $param["RoomId"] = $roomId;
            ksort($param);
            $signStr = "GETtrtc.tencentcloudapi.com/?";
            foreach ($param as $key => $value) {
                $signStr = $signStr . $key . "=" . $value . "&";
            }
            $signStr = substr($signStr, 0, -1);

            $signature = base64_encode(hash_hmac("sha1", $signStr, 'h19SXR7XhfRzpSqzta3PWKtDI7k7OUKv', true));

            $param['Signature'] = $signature;
            $params = http_build_query($param);
            $url = 'https://trtc.tencentcloudapi.com' . '?' . $params;
            $curl = curl_init();
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
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }

    }

    /**
     * 单个帐号导入接口
     * @param string $Identifier 用户名，长度不超过32字节
     * @param string $Nick 用户昵称
     * @param string $FaceUrl 用户头像 URL
     * @param string $Type 帐号类型，开发者默认无需填写，值0表示普通帐号，1表示机器人帐号
     * @return mixed https://cloud.tencent.com/document/product/269/1608
     */
    public function userImport($Identifier, $Nick = '', $FaceUrl = '', $Type = 0)
    {
        try {
            $resp = $this->requestPost('/im_open_login_svc/account_import', [
                "Identifier" => $Identifier . '',
                "Nick" => $Nick,
                "FaceUrl" => $FaceUrl,
                "Type" => $Type
            ]);
            if ($resp['ErrorCode'] == 0) {
                $userSign = $this->userSign($Identifier);
                return $this->succeed($userSign);
            }
            return $this->failure(1, 'IM账户注册失败', $resp);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }

    }

    /**
     * 批量帐号导入接口
     * @param array $Accounts 用户名，单个用户名长度不超过32字节，单次最多导入100个用户名
     * @return mixed
     */
    public function userMultiImport($Accounts = [])
    {
        return $this->requestPost('/im_open_login_svc/account_import', [
            "Accounts" => $Accounts,
        ]);

    }

    /**
     * 帐号登录态失效接口
     * @param array $accounts
     * @return mixed
     */
    public function userKick($Identifier = [])
    {
        return $this->requestPost('/im_open_login_svc/kick', [
            "Identifier" => $Identifier
        ]);

    }

    /**
     * 获取用户在线状态
     * @param array $accounts
     * @return mixed
     */
    public function userStatus($accounts = [])
    {
        try {
            $result = $this->requestPost('/openim/querystate', [
                "To_Account" => $accounts
            ]);
            return array(true, $result);
        } catch (\Exception $exception) {
            return array(false, '用户IM状态查询异常！');
        }

    }

    /**
     * 拉取资料
     * @param array $accounts
     * @return mixed
     */
    public function userGetInfo($To_Account, $TagList)
    {
        return $this->requestPost('/profile/portrait_get', [
            "To_Account" => $To_Account,
            "TagList" => $TagList,
        ]);

    }

    /**
     * 设置资料
     * @param array $accounts
     * @return mixed
     */
    public function userSetInfo($From_Account, $nickNmae, $headPic, $sex = 'Gender_Type_Unknown')
    {
        return $this->requestPost('/profile/portrait_set', [
            "From_Account" => $From_Account,
            "ProfileItem" => [
                ["Tag" => 'Tag_Profile_IM_Nick', "Value" => $nickNmae,],
                ["Tag" => 'Tag_Profile_IM_Image', "Value" => $headPic],
                ["Tag" => 'Tag_Profile_IM_Gender', "Value" => $sex],
            ],

        ]);

    }

    /**
     * 单发单聊消息
     * @param $To_Account 消息接收方 Identifier
     * @param int $MsgRandom 消息随机数，由随机函数产生（标记该条消息，用于后台定位问题）
     * @param array $MsgBody
     * @param $MsgType TIM 消息对象类型，目前支持的消息对象包括：TIMTextElem(文本消息)，TIMFaceElem(表情消息)，TIMLocationElem(位置消息)，TIMCustomElem(自定义消息)
     * @param $MsgContent 对于每种 MsgType 用不同的 MsgContent 格式，具体可参考 消息格式描述
     * @param $SyncOtherMachine
     * @param $From_Account 消息发送方 Identifier（用于指定发送消息方帐号）
     * @param $MsgLifeTime
     * @param $MsgTimeStamp 消息时间戳，UNIX 时间戳（单位：秒）
     * @param $OfflinePushInfo
     * @return mixed https://cloud.tencent.com/document/product/269/2282
     */
    public function sendMsg($To_Account, $MsgRandom, $MsgType, $MsgContent, $From_Account = '', $SyncOtherMachine = 1, $MsgLifeTime = 604800, $MsgTimeStamp = '', $OfflinePushInfo = '')
    {

        $data = [
            "To_Account" => $To_Account,
            "MsgRandom" => $MsgRandom,
            "MsgBody" => [[
                "MsgType" => $MsgType,
                "MsgContent" => $MsgContent
            ]],
            "SyncOtherMachine" => $SyncOtherMachine,
//            "From_Account" => $From_Account,
            "MsgLifeTime" => $MsgLifeTime,
//            "MsgTimeStamp" => time(),
//            "OfflinePushInfo" => $OfflinePushInfo,
        ];
        if ($From_Account) {
            $data['From_Account'] = $From_Account;
        }
        if ($MsgType == 'TIMCustomElem') {
            $data['SyncOtherMachine'] = 2;
            $data['MsgLifeTime'] = 0;

        }
        if ($MsgType == 'TIMTextElem') {
            $data['OfflinePushInfo'] = [
                "PushFlag" => 0,
                "Title" => "这是推送标题",
                "Desc" => "这是离线推送内容",
                "Ext" => "这是透传的内容",
                "AndroidInfo" => [
                    "Sound" => "android.mp3"],
                "ApnsInfo" => [
                    "Sound" => "apns.mp3",
                    "BadgeMode" => 1,
                    "Title" => "apns title",
                    "SubTitle" => "apns subtitle",
                    "Image" => "www.image.com"
                ]
            ];
        }
        return $this->requestPost('/openim/sendmsg', $data);

    }

    public function addRoom($ToAccount, $Data = [])
    {
        $MsgRandom = rand(1000000, 9999999);
        $MsgType = 'TIMCustomElem';
        $MsgContent = ['Data' => json_encode($Data)];
        return $this->sendMsg($ToAccount, $MsgRandom, $MsgType, $MsgContent);


    }

    //HTTP请求（支持HTTP/HTTPS，支持GET/POST）
    function requestPost($url, $data = null)
    {
//        $usersig = $this->userSign($this->config['identifier']);
        $params = http_build_query([
            'sdkappid' => $this->config['appid'],
            'identifier' => $this->config['identifier'],
            'usersig' => $this->config['token'],
            'random' => rand(10000000, 99999999) . rand(10000000, 99999999) . rand(10000000, 99999999) . rand(10000000, 99999999),
            'contenttype' => 'json',
        ]);
        $url = $this->config['base_url'] . $url . '?' . $params;
        $curl = curl_init();
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

}
