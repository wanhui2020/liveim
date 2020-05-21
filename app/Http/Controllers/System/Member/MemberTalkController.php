<?php
/**
 *  会员聊天管理控制器
 */

namespace App\Http\Controllers\System\Member;

use App\Facades\MemberFacade;
use App\Facades\RecordFacade;
use App\Http\Controllers\Controller;
use App\Models\MemberAccount;
use App\Models\MemberExtend;
use App\Models\MemberGift;
use App\Models\MemberInfo;
use App\Models\MemberRecord;
use App\Models\MemberTalk;
use App\Models\SystemData;
use App\Models\SystemGift;
use App\Repositories\MemberGiftRepository;
use App\Repositories\MemberTalkRepository;
use App\Services\ReportService;
use App\Utils\Helper;
use App\Utils\SelectList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MemberTalkController extends Controller
{
    public function __construct(MemberTalkRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        $type = SelectList::talkType();
        $status = SelectList::talkStatus();
        return view('member.talk.index', compact('type', 'status'));
    }

    /*
    *  显示列表(获取数据)
    * */
    public function lists()
    {
        try {
            $list = $this->repository->lists(null, ['member', 'tomember']);
            return $this->paginate($list);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * 获取总收益和总消费
     */
    public function getcCollect(Request $request,ReportService $report)
    {
        try{
            $data = $report->getDataCount($request);
            return $this->succeed($data);
        }catch (\Exception $e){
            $this->exception($e);
            return false;
        }
    }
    /*
     * 添加视图
     * */
    public function create()
    {
        $type = SelectList::talkType();
        $member = MemberInfo::where(['status' => 1, 'sex' => 0])->get(['id', 'code', 'user_name', 'nick_name']); //发起会员
        $tomember = MemberInfo::where(['status' => 1, 'sex' => 1])->get(['id', 'code', 'user_name', 'nick_name']); //接收会员（主播）
        return view('member.talk.create', compact('member', 'tomember', 'type'));
    }

    /*
    * 保存到数据库
    * */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $memberId = $data['member_id']; //发起会员
            $toMemberId = $data['to_member_id']; //接收主播
            $type = $data['type']; //类型（0.文本 1.语音 2.视频）
            if ($type != 0) {
                //如果是语音视频要先判断主播是否空闲
                $zbInfo = MemberInfo::find($toMemberId);
                if ($zbInfo->online_status == 0) {
                    return $this->failure(1, '对不起，该主播不在线！');
                }
                if ($zbInfo->vv_busy == 1) {
                    return $this->failure(1, '对不起，该主播正在通话中！');
                }
            }
            $data = new MemberTalk();
            $data['member_id'] = $memberId;
            $data['to_member_id'] = $toMemberId;
            $data['type'] = $type;
            $data['channel_code'] = Helper::getNo();
            return MemberFacade::addTalkOrder($data); //调用方法

        } catch (\Exception $ex) {

            return $this->exception($ex);
        }
    }


    /**
     * 删除
     * @param Request $request
     * @return array|mixed
     */
    public function destroy(Request $request)
    {
        try {
            $result = $this->repository->destroy($request->ids);
            return $result;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /*
     * 处理保存
     * */
    public function dealSave(Request $request)
    {
        try {
            $data = $this->repository->find($request->id); //聊天对象
            $dealStatus = $request->status; //处理方式
            if (!isset($data)) {
                return $this->validation('未找到处理订单！');
            }
            return MemberFacade::dealTalkOrder($data, $dealStatus); //调用处理方法

//            $memberId = $data['member_id']; //发起会员
//            $toMemberId = $data['to_member_id']; //接收主播
//            $type = $data['type']; //类型（0.文本 1.语音 2.视频）
//            $gold = $data['price'];
//
//            if ($dealStatus != 2) {
//                //拒接
//                $data['status'] = 2; //直接结束
//                return $this->repository->update($data);
//            }
//
//            $memberAccount = MemberAccount::where('member_id', $memberId)->first(); //会员
//            $zhuboAccount = MemberAccount::where('member_id', $toMemberId)->first(); //主播账户
//            $zhuboExtend = MemberExtend::where('member_id', $toMemberId)->first(); //主播扩展信息
//            $rate = $zhuboExtend->talk_rate; //默认取主播自身的，如果未单独设置，则取平台默认值，取费率
//            if ($rate == 0) {
//                $rate = $config->rate;
//            }
//
//            if ($type == 0) {
//                //待处理
//                if ($data['status'] == 0 && $dealStatus == 3) {
//                    $data['status'] = 2; //直接结束
//                    $data['times'] = 1; //条
//                    $data['begin_time'] = Helper::getNowTime();
//                    $data['end_time'] = Helper::getNowTime();
//
//                    //结束文本消息，直接扣款
//                    //判断文本消息达到免费条数
//                    $freeCount = $config->free_text; //免费笔数
//                    $yfcount = $this->repository->findWhere(['member_id' => $memberId, 'to_member_id' => $toMemberId, 'type' => 0])->count();
//                    if ($yfcount > $freeCount) {
//                        //超出才收费
//                        //1.会员扣除资费默认先扣可用余额
//                        $beforeAmount = $memberAccount->surplus_gold;
//                        $afterAmount = $beforeAmount - $gold;
//
//                        $record = new MemberRecord();
//                        $record->member_id = $memberId;
//                        $record->type = 11;//11.普通消息消费 12.语音消费 13.视频消费
//                        $record->account_type = 1; //账户类型
//                        $record->amount = -$gold; //发生金额
//                        $record->freeze_amount = 0;//冻结金额
//                        $record->before_amount = $beforeAmount;//变动前额
//                        $record->balance = $afterAmount;//实时余额
//                        $record->status = 1;//交易成功
//                        $record->remark = '文本消息收费';//交易备注
//
//                        //2.主播收益
//                        $sxf = round($gold * ($rate / 100)); //手续费，四舍五入
//                        $syGold = $gold - $sxf; //主播收益
//                        $beforeAmount = $zhuboAccount->surplus_gold;
//
//                        $zbRecord = new MemberRecord();
//                        $zbRecord->member_id = $toMemberId; //主播ID
//                        $zbRecord->type = 16;//类型
//                        $zbRecord->account_type = 1; //账户类型
//                        $zbRecord->amount = $syGold; //发生金额
//                        $zbRecord->freeze_amount = 0;//冻结金额
//                        $zbRecord->before_amount = $beforeAmount;//变动前额
//                        $zbRecord->balance = $beforeAmount + $syGold;//实时余额
//                        $zbRecord->status = 1;//交易成功
//                        $zbRecord->remark = '文本消息收益';//交易备注
//
//                        $data['amount'] = $gold;
//                        $data['total_profit'] = $syGold;
//
//                        $result = RecordFacade::addRecord($record, $zbRecord); //调用新增资金流水
//                        if (!$result['status']) {
//                            return $result;
//                        }
//                    } else {
//                        $data['amount'] = 0;
//                    }
//                }
//            } else {
//
//                //语音或视频
//                if ($data['status'] == 0 && $dealStatus == 1) {
//                    //开始聊天
//                    $data['status'] = 1; //开始
//                    $data['begin_time'] = Helper::getNowTime();
//                    $data['channel_code'] = Helper::getNo(); //接通后返回通道编号
//
//                    $memberAccount->notuse_gold += $gold; //冻结1分钟
//                    $memberAccount->save();
//                    $zhuboInfo = MemberInfo::find($toMemberId);
//                    $zhuboInfo->vv_busy = 1; //忙碌
//                    $zhuboInfo->save();
//
//                }
//                if ($data['status'] == 1 && $dealStatus == 3) {
//
//                    //结束聊天，进行结算
//                    $nowTime = date("Y-m-d H:i:s", time());
//                    $totalS = strtotime($nowTime) - strtotime($data->begin_time); //总秒数
//                    $totalM = ceil($totalS / 60); //聊天总分钟数
//                    $totalAmount = $data->price * $totalM; //总共消费
//
//                    //增加流水记录
//                    //1.会员扣除资费默认先扣可用余额
//                    $beforeAmount = $memberAccount->surplus_gold;
//                    $afterAmount = $beforeAmount - $totalAmount;
//
//                    $record = new MemberRecord();
//                    $record->member_id = $memberId;
//                    $record->type = $type == 1 ? 12 : 13;//11.普通消息消费 12.语音消费 13.视频消费
//                    $record->account_type = 1; //账户类型
//                    $record->amount = -$totalAmount; //发生金额
//                    $record->freeze_amount = 0;//冻结金额
//                    $record->before_amount = $beforeAmount;//变动前额
//                    $record->balance = $afterAmount;//实时余额
//                    $record->status = 1;//交易成功
//                    $record->remark = $type == 1 ? '语音通话消费' : '视频通话消费';//交易备注
//
//                    //2.主播收益
//                    $sxf = round($totalAmount * ($rate / 100)); //手续费，四舍五入
//                    $syGold = $totalAmount - $sxf; //主播收益
//
//                    $beforeAmount = $zhuboAccount->surplus_gold;
//                    $afterAmount = $beforeAmount + $syGold;
//
//                    $zbRecord = new MemberRecord();
//                    $zbRecord->member_id = $toMemberId; //主播ID
//                    $zbRecord->type = $type == 1 ? 17 : 18;//类型
//                    $zbRecord->account_type = 1; //账户类型
//                    $zbRecord->amount = $syGold; //发生金额
//                    $zbRecord->freeze_amount = 0;//冻结金额
//                    $zbRecord->before_amount = $beforeAmount;//变动前额
//                    $zbRecord->balance = $afterAmount;//实时余额
//                    $zbRecord->status = 1;//交易成功
//                    $zbRecord->remark = $type == 1 ? '语音通话收益' : '视频通话收益';//交易备注
//
//                    $data['amount'] = $totalAmount;
//                    $data['times'] = $totalS; //总秒数
//                    $data['end_time'] = $nowTime; //结束日期
//                    $data['status'] = 2; //已结束
//                    $data['total_profit'] = $syGold; //总收益
//
//                    $result = RecordFacade::addRecord($record, $zbRecord, true); //调用新增资金流水
//                    if (!$result['status']) {
//                        return $result;
//                    }
//                    //主播改为空闲
//                    $zhuboInfo = MemberInfo::find($toMemberId);
//                    $zhuboInfo->vv_busy = 0; //空闲
//                    $zhuboInfo->save();
//
//                }
//
//            }
//            return $this->repository->update($data);

        } catch (\Exception $ex) {

            return $this->exception($ex);
        }
    }

}