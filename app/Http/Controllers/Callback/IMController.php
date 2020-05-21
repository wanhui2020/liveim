<?php

namespace App\Http\Controllers\Callback;

use App\Facades\BaseFacade;
use App\Facades\JhPayFacade;
use App\Facades\MemberFacade;
use App\Http\Controllers\Controller;
use App\Models\MemberInfo;
use App\Models\MemberTalk;
use App\Models\SystemConfig;
use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IMController extends Controller
{

    public function index(Request $request)
    {
        try {
            $retData = $request->all();
//            Log::info('IM回调',[$retData]);
//            $this->logs('IM回调', $retData);

            //逻辑处理处理状态错误！无dealstatus
            $callbackCommand = $retData['CallbackCommand'];
            /**
             * 发送单聊消息之前回调
             */
            if ($callbackCommand == 'C2C.CallbackBeforeSendMsg') {
                $From_Account = $retData['From_Account'];
                $To_Account = $retData['To_Account'];
                $msgBody = $retData['MsgBody'][0]; //内容
                $msgType = $msgBody['MsgType']; //发送之前
                $msgContent = $msgBody['MsgContent']; //
//                Log::info('测试回调',[$msgBody,$msgType,$msgContent]);
                $member_id = $From_Account;
                $member = MemberInfo::find($member_id);
                if (!isset($member)){
                    return $this->validation('未找到该用户');
                }
                //后台禁用用户状态不让发言
                if ($member->status == 0) {
                    return [
                        'ActionStatus' => 'ok',
                        'ErrorInfo' => '',
                        'ErrorCode' => 1, //拒绝发言
                    ];
                }
                if ($member->sex === 1 && $member->selfie_check === 0) {
                    return [
                        'ActionStatus' => 'ok',
                        'ErrorInfo' => '',
                        'ErrorCode' => 1, //拒绝发言
                    ];
                }
                //添加聊天订单
                if ($msgType == 'TIMTextElem') {
                    $text = $msgContent['Text'];
//                    if (Cache::has('SystemConfig')) {
//                        $config = Cache::get('SystemConfig')->keyword;
//                    } else {
//                        $config = SystemConfig::first()->keyword;
//                    }
//                    if (isset($config)) {
//                        $a = explode(',', $config);
//                        foreach ($a as $item) {
//                            if (strpos($text, $item) !== false){
//                                $text = str_replace($item,'*',$text);
//                            }
//                        }
//                    }
                    if (!BaseFacade::keyword($text)) {
                        return [
                            'ActionStatus' => 'ok',
                            'ErrorInfo' => '发送失败:内容屏蔽',
                            'ErrorCode' => 120001
                        ];
//                        return [
//                            'ActionStatus' => 'ok',
//                            'ErrorInfo' => '',
//                            'ErrorCode' => 1, //拒绝发言
//                        ];
                    }
                    //添加订单
                    $data = new MemberTalk();
                    $data['member_id'] = $From_Account;
                    $data['to_member_id'] = $To_Account;
                    $data['type'] = 0;
                    $data['channel_code'] = Helper::getNo();
                    $ret = MemberFacade::addTalkOrder($data);
                    //发送之前回调
                    $status = 0;
                    $bodyItem = array();
                    if (!$ret['status']) {
                        $status = 1;
                        $this->logs('发消息添加订单', $ret['msg']);
                    } else {
                        //成功才进行发送
                        $bodyItem = array(
                            'MsgType' => 'TIMTextElem',
                            'MsgContent' => array(
                                'Text' => $text,
                            )
                        );
                    }

                    $body[] = $bodyItem;
                    return [
                        'ActionStatus' => 'ok',
                        'ErrorInfo' => '发送失败:' . $body,
                        'ErrorCode' => 120001
                    ];
//                    $ret = array(
//                        'ActionStatus' => 'ok',
//                        'ErrorInfo' => '',
//                        'ErrorCode' => $status, //拒绝发言
//                        'MsgBody' => $body
//                    );
//                    return $ret;
                }
                //自定义
                if ($msgType == 'TIMCustomElem') {
                    //接收到自定义消息
                    $data = json_decode($msgContent['Data'], true);
                    if (isset($data)) {
                        $key = $data['key']; //指令
                        if (in_array($key, ['video', 'voice', 'over', 'calling'])) {
                            $status = $data['type']; // 订单状态指令
                            //详查订单
                            $errorCode = 0;
                            if ($status != -1) {
                                $orderid = $data['roomid']; //订单ID
                                //需要进行订单处理
                                $order = MemberTalk::find($orderid);
                                if (isset($order)) {
                                    if ($order->status == 2) {
                                        $errorCode = 0;
                                    } else {
                                        $result = MemberFacade::dealTalkOrder($order, $status);
                                        if (!$result['status']) {
                                            $errorCode = 1;
                                            $this->logs('处理通话订单失败', $result['msg']);
                                        }
                                    }
                                }
                            }
                            //发送
                            $ret = array(
                                'ActionStatus' => 'ok',
                                'ErrorInfo' => '',
                                'ErrorCode' => $errorCode,//1.拒绝发言
                                'MsgBody' => []
                            );
                            return $ret;
                        }
                    }
                }
            }
            if ($callbackCommand == 'State.StateChange') {
                //状态变更(进行上下线处理)
                $info = $retData['Info'];
                $To_Account = $info['To_Account'];
                $Action = $info['Action'];
                $member = MemberInfo::find($To_Account);
                if (isset($member)) {
                    if ($Action == 'Logout') {
                        //下线
                        $member->online_status = 0;
                        Log::info('我是IM回调登出离线',[$member->nick_name]);
                        $member->vv_busy = 0;
                    }
                    if ($Action == 'Disconnect') {
                        //下线
                        $member->online_status = 0;
                        Log::info('我是Kill掉APP离线',[$member->nick_name]);
                        $member->vv_busy = 0;
                    }
                    //上线
                    if ($Action == 'Login') {
                        $member->online_status = 1;
                    }
                    $member->save();
                }
            }
        } catch (\Exception $exception) {
            $this->logs('IM监听异常', $exception->getMessage(), 'error');
            $this->exception( $exception,'IM监听回调异常');
        }
    }
}
