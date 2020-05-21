<?php

namespace App\Http\Controllers\Api\Member;

use App\Facades\ImFacade;
use App\Facades\MemberFacade;
use App\Http\Controllers\Api\ApiController;
use App\Jobs\TalkJob;
use App\Models\MemberExtend;
use App\Models\MemberInfo;
use App\Models\MemberTakeNow;
use App\Models\MemberTalk;
use App\Repositories\MemberInfoRepository;
use App\Repositories\MemberTalkRepository;
use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

//会员主播通话管理
class MemberTalkController extends ApiController
{

    public function __construct(MemberTalkRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 会员发起通话订单
      * */
    public function addTalkOrder(Request $request, MemberInfoRepository $memberInfoRepository)
    {
        try {
            $member = $request->user('api');
            /**
             * 判断是女性用户并且是自拍认证通过的用户才能发送消息
             */
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            if ($member->sex === 1 && $member->selfie_check === 0){
                /**
                 * 女性用户未进行自拍认证不能发送消息
                 */
                return $this->validation('请先进行自拍认证！');
            }
            //可修改字段
            $toid = $request['toid']; //通话对象
            $type = $request['type']; //通话类型（0.文本 1.语音 2.视频）

            if (!isset($toid) || !isset($type)) {
                return $this->validation('参数错误，请传入必填参数值！');
            }
            if (!in_array($type, [0, 1, 2])) {
                return $this->validation('参数错误，请传入正确的通话类型参数！');
            }
            if ($member->id == $toid) {
                return $this->validation('不能与自己创建通话！');
            }
            $zbinfo = $memberInfoRepository->find($toid);
            if (!isset($zbinfo)) {
                return $this->validation('未找到通话对象！');
            }
            //判断这个主播有没有还没结束的订单
            $count1 = MemberTalk::where('to_member_id', $toid)->where('status', '<', 2)->count();
            if ($count1 > 0) {
                return $this->validation('线路繁忙，请稍后再试！');
            }
            if ($member->vv_busy == 1){
                return $this->validation('线路繁忙，请稍后再试！');
            }
            $count2 = MemberTalk::where('member_id', $member->id)->where('status', '<', 2)->count();
            if ($count2 > 0) {
                return $this->validation('线路繁忙，请稍后再试！');
            }
            if ($type != 0) {
                //语音或视频通话需判断主播的状态
                if ($zbinfo->online_status == 0) {
                    return $this->validation('当前主播不在线！');
                }
                if ($zbinfo->vv_busy == 1) {
                    return $this->validation('当前主播忙碌中！');
                }
                //核实主播IM否在线
                $account = [(string)$zbinfo->id];
                list($ret, $data) = ImFacade::userStatus($account);
                if (!$ret) {
                    return $this->validation($data);
                }
                if ($data['ActionStatus'] == "OK") {
                    $queryStatus = $data['QueryResult'][0]['State'];
                    if ($queryStatus == "Offline") {
                        $zbinfo->online_status = 0;
                        Log::info('我是发起通话的时候IM回调时离线',[$zbinfo->nick_name]);
                        $zbinfo->save();
                        return $this->validation('当前主播不在线！');
                    }
                }
            }
            $data = new MemberTalk();
            $data['member_id'] = $member->id;
            $data['to_member_id'] = $toid;
            $data['type'] = $type;
            $data['channel_code'] = Helper::getNo();
            return MemberFacade::addTalkOrder($data); //调用方法

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }

    }

    /*
    * 订单处理
    * */
    public function dealTalkOrder(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            $orderid = $request['orderid']; //三方订单号
            $status = $request['status']; //订单处理状态（1,2,3）
            if (!isset($orderid) || !isset($status)) {
                return $this->validation('请输入所有必填参数！');
            }
            if (!in_array($status, [1, 2, 3])) {
                return $this->validation('处理状态参数错误！');
            }
            $data =  $this->repository->find($orderid);
            if (!isset($data)) {
                return $this->validation('未找到处理订单！');
            }
            if ($status === 3){
                if ($data['status'] != 1){
                    return $this->validation('该订单未在通话中！');
                }
            }
//            return $this->dispatch(new TalkJob($data['id']));
            return MemberFacade::dealTalkOrder($data, $status); //调用处理方法

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
    * 订单处理
    * */
    public function checkTalkOrder(Request $request)
    {
        try {
            $orderid = $request['orderid']; //订单号
            if (!isset($orderid)) {
                return $this->validation('请输入所有必填参数！');
            }
            return MemberFacade::checkTalkOrder($orderid); //调用处理方法

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     * 通过ID查询通话详情
     * */
    public function getTalkInfoById(Request $request)
    {
        try {
            $orderid = $request['orderid']; //订单号
            if (!isset($orderid)) {
                return $this->validation('请输入所有必填参数！');
            }
            $talk = $this->repository->find($orderid);
            if (!isset($talk)) {
                return $this->validation('未找到通话订单！');
            }
            if ($talk->status == 1) {
                //通话中
                //实时计算通话时间、消费和收益
                $nowTime = date("Y-m-d H:i:s", time());
                $totalS = strtotime($nowTime) - strtotime($talk->begin_time); //总秒数
                $totalM = ceil($totalS / 60); //聊天总分钟数
                $totalAmount = $talk->price * $totalM; //总消费
                $syGold = 0;
                if ($totalAmount > 0) {
                    //主播收益
                    $zhuboExtend = MemberExtend::where('member_id', $talk['to_member_id'])->first(); //主播配置
                    $rate = $zhuboExtend->talk_rate; //默认取主播自身的，如果未单独设置，则取平台默认值，取费率
                    if ($rate == 0) {
                        $config = Cache::get('SystemBasic');
                        $rate = $config->rate;
                    }
                    $sxf = round($totalAmount * ($rate / 100)); //手续费，四舍五入
                    $syGold = $totalAmount - $sxf; //主播收益
                }
            } else {
                $totalAmount = $talk->amount;
                $syGold = $talk->total_profit;
                $times = $talk->times;
            }
            $ret = array(
                'status' => $talk->status,
                'status_cn' => $talk->status_cn,
                'times' => $totalS,
                'zbsy_gold' => $syGold,
                'total_consume' => $totalAmount
            );
            return $this->succeed($ret, '操作成功');
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }


    public function testIm(Request $request)
    {
        $code = $request['code'];
        $key = $request['key'];
        //发送通知
        $param = array(
            'key' => $key,
            'roomid' => 1,
            'nick_name' => '小骚棒',
            'pic' => 'http://b-ssl.duitang.com/uploads/item/201608/21/20160821230024_MyCYK.thumb.700_0.jpeg'
        );
        ImFacade::addRoom((string)$code, $param);
        return $this->succeed($code);
    }

    /**
     * 查询订单状态 返回整个订单的信息
     */
    public function query(Request $request)
    {
        try {
            /**
             * 根据API  用户的ID
             * 查询订单表
             * 如果有呼叫中的就返整个订单信息
             * 没有就返false
             */
            $member = $request->user('api');
            $dealTalk = MemberTalk::where(function ($query) use ($member) {
                $query->orWhere('to_member_id', $member->id);
                $query->orWhere('to_member_id', $member->id);
            })->whereNotIn('status', [0])->whereNotIn('type', [0])->first();
            return $this->succeed($dealTalk, '返回订单查询成功!');
        } catch (\Exception $ex) {
            return $this->exception($ex, '查询订单异常，请联系管理员');
        }
    }
}
