<?php

namespace App\Services;

use App\Facades\RecordFacade;
use App\Models\MemberAccount;
use App\Models\MemberDayCount;
use App\Models\MemberFhscore;
use App\Models\MemberInfo;
use App\Models\MemberInviteAward;
use App\Models\MemberMlscore;
use App\Models\MemberRecharge;
use App\Models\MemberRecord;
use App\Models\MemberScore;
use App\Models\MemberScoreRule;
use App\Traits\ResultTrait;
use App\Utils\SelectList;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

//会员资金流水服务
class RecordService
{
    use ResultTrait;

    //添加资金流水
    public function addRecord($record, $record2 = null, $jiedong = false,$talk = 0)
    {
        DB::beginTransaction();
        try {
            if ($talk === 1){
                $rec1 = MemberRecord::where('member_id',$record['member_id'])->where('talk_id',$record['talk_id'])->first();
                if (isset($rec1)){
                    DB::rollBack();
                    return $this->validation('流水已经生成该订单了');
                }
//                $rec2 = MemberRecord::where('member_id',$record2['member_id'])->where('talk_id',$record2['talk_id'])->first();
//                if (isset($rec2)){
//                    DB::rollBack();
//                    return $this->failure('流水已经生成该订单了');
//                }
            }
            $memberId = $record['member_id'];
            $amount = $record->amount; //流水变动金额
            $memberInfo = MemberInfo::find($memberId);
            $memberAccount = $memberInfo->account; //MemberAccount::where('member_id', $memberId)->first();
            //如果是扣除金币才判断会员余额是否足够
            if ($amount < 0) {
                if ($record->account_type == 1 && $memberAccount->balance_gold < $amount) {
                    DB::rollBack();
                    return $this->failure(1, '可用金币余额不足!');
                }
                if ($record->account_type == 2 && $memberAccount->notuse_gold < $amount) {
                    DB::rollBack();
                    return $this->failure(1, '不可提现金币余额不足!');
                }
            }
            //1.添加用户资金流水记录
            $record->save();
            //2.资金账户变动
            $balance = $record->balance; //实时余额
            if ($record->account_type == 1) {
                $memberAccount->surplus_gold += $amount;
            } else if ($record->account_type == 0) {
                $memberAccount->surplus_rmb += $amount; //人民币账户
            } else {
                $memberAccount->notuse_gold += $amount;
            }
            if ($jiedong) {
                //需要解冻不可用账户
//                $notuse_gold = $memberAccount->notuse_gold - abs($amount);
                $memberAccount->notuse_gold = 0;//$notuse_gold < 0 ? 0 : $notuse_gold;
            }

            //3.其他
            if ($record->type == 7) {
                //补签累加1
                $memberAccount->bq_count += 1;
            }
            if ($record->type == 3 && $memberInfo->sex == 0) {
                //会员兑换后，要扣除可兑换金币账户
                $memberAccount->cantx_gold -= $amount;
            }
            if (($record->type == 21 || $record->type == 24) && $memberInfo->sex == 0) {
                //会员邀请注册赠送时,或下级充值奖励时，可增加可兑换金币账户
                $memberAccount->cantx_gold += $amount;
            }
            $memberAccount->save();


            if ($record2 != null) {
                //收入/
                $memberId = $record2['member_id'];
                $amount = $record2->amount; //流水变动金额
                $zbAccount = MemberAccount::where('member_id', $memberId)->first();
                //1.添加用户资金流水记录
                $record2->save();
                //2.资金账户变动
                if ($record2->account_type == 0) {
                    $zbAccount->surplus_rmb += $amount;
                } else {
                    $zbAccount->surplus_gold += $amount;
                }
                $zbAccount->save();

            }


            DB::commit();
            return $this->succeed();

        } catch (\Exception $ex) {
            DB::rollBack();

            return $this->exception($ex);
        }
    }

    /*
     * 会员实时统计
     * 1充值金币
     * 2送礼物
     * 3兑换
     * 4退款
     * 5冻结资金
     * 6解冻资金
     * 7补签
     * 8后台管理资金
     * 9收到礼物
     * 10自拍奖励
     * 11普通消息消费
     * 12语音消费
     * 13视频消费
     * 14看颜照
     * 15看视频
     * 16普通消息收益
     * 17语音通话收益
     * 18视频通话收益
     * 19颜照被查看收益
     * 20视频被查看收益
     * 21邀请人员充值奖励
     * 22购买VIP
     * 23注册赠送币
     * 24注册赠送邀请人
     * 25冻结已使用
     * 26解冻已使用
     * 27解冻音视频通话币
     * 28付费换衣服
     * 29换衣服收益
     * 30换衣服退款
     * 31换衣服退还收益
     * 32打赏
     * 33收到打赏
     * 34邀约收益
     * -1提现
     * */
    public function memberDayCount($data)
    {
        try {
            $memberId = $data['member_id']; //会员ID
            $type = $data['type']; //类型
            $dayInt = date('Ymd'); //日期
            $dayCount = MemberDayCount::firstOrNew(['member_id' => $memberId, 'dayint' => $dayInt]);
            $amount = abs($data['amount']);
            if (in_array($type, [1])) {
                $dayCount['rec_gold'] += $amount; //充值金币
            }
            if (in_array($type, [2, 7, 11, 12, 13, 14, 15, 28, 32])) {
                $dayCount['consume_gold'] += $amount; //消费金币
            }
            if (in_array($type, [9, 16, 17, 18, 19, 20, 29, 33])) {
                $dayCount['profit_gold'] += $amount; //收益金币
            }
            if (in_array($type, [10, 21, 23, 24])) {
                $dayCount['award_gold'] += $amount; //奖励金币
            }
            if (in_array($type, [34])) {
                $dayCount['profit_money'] += $amount; //收益金额
            }
            if (in_array($type, [-1])) {
                $dayCount['take_money'] += $amount; //提现
            }
            $dayCount->save();
        } catch (\Exception $ex) {
            $this->exception($ex);
        }
    }

    /*
     * 执行会员积分统计
     * */
    public function memberScoreCount($data)
    {
        try {
            $memberId = $data['member_id']; //会员ID
            $type = $data['type']; //类型
            //查询规则
            $ruleType = $this->GetRuleType($type);
            //查询定义规则
            $ruleModel = MemberScoreRule::where(['desc' => $ruleType, 'status' => 1])->first();
            if (isset($ruleModel)) {
                $score = $ruleModel->score;
                if ($score == 0) {
                    $score = abs($data['amount']);
                }
                $scoreType = $ruleModel->type; //积分类型
                if ($scoreType == 0) {
                    //普通积分
                    $model = new MemberScore();
                }
                if ($scoreType == 2) {
                    //魅力积分
                    $model = new MemberMlscore();
                }
                if ($scoreType == 1) {
                    //富豪
                    $model = new MemberFhscore();
                }
                $model['member_id'] = $memberId;
                $model['type'] = $ruleType;
                $model['score'] = $score;
                $ret = $model->save();
            }
        } catch (\Exception $ex) {
            $this->exception($ex);
        }
    }

    /*
     * 账户类型转为积分规则类型
     * */
    private function GetRuleType($type)
    {
        $ruleType = 0;
        switch ($type) {
            case 1: //充值
                $ruleType = 101;
                break;
            case 22: //充值VIP
                $ruleType = 102;
                break;
            case 15: //看视频
                $ruleType = 103;
                break;
            case 14: //看颜照
                $ruleType = 104;
                break;
            case 13: //视频
                $ruleType = 105;
                break;
            case 12: //语音
                $ruleType = 106;
                break;
            case 2: //送礼物
                $ruleType = 107;
                break;
            case 28: //付费换衣
                $ruleType = 108;
                break;
//            case 14: //付费邀约（）
//                $ruleType = 109;
//                break;
            case 20: //被查看视频
                $ruleType = 201;
                break;
            case 19: //被查看颜照
                $ruleType = 202;
                break;
            case 18: //收到视频
                $ruleType = 203;
                break;
            case 17: //收到语音
                $ruleType = 204;
                break;
            case 9: //收到礼物
                $ruleType = 205;
                break;
            case 29: //换衣收益
                $ruleType = 206;
                break;
            case 34: //邀约收益
                $ruleType = 207;
                break;
        }
        return $ruleType;
    }


    /*
     * 邀请奖励处理
     * */
    public function memberInviteAward($data)
    {
        try {

            //判断收入是否给上级提成
            $type = $data['type']; //收入类型
            $quantity = abs($data['amount']); //金币数量
            $memberId = $data['member_id'];

            //下级消费奖励
//            if (in_array($type, [2, 11, 12, 13, 14, 15, 28])) {
//                $config = Cache::get('SystemBasic'); //取平台配置
//                $consume_rate = $config->consume_rate; //邀请人下级消费奖励比例
//                if ($consume_rate > 0) {
//                    $memberInfo = MemberInfo::find($memberId); //会员
//                    //奖励金币
//                    $gold = $quantity * $consume_rate / 100;
//                    //查询推荐人
//                    $tjrid = $memberInfo->pid;
//                    $tjsAccount = MemberAccount::where('member_id', $tjrid)->first();
//                    if (isset($tjsAccount)) {
//
//                        $beforeAmount = $tjsAccount->surplus_gold;
//                        $afterAmount = $beforeAmount + $gold;
//                        //流水记录
//                        $record = new MemberRecord();
//                        $record->member_id = $tjsAccount->member_id;
//                        $record->type = 36;//下级消费奖励
//                        $record->account_type = 1; //账户类型
//                        $record->amount = $gold; //发生金额
//                        $record->freeze_amount = 0;//冻结金额
//                        $record->before_amount = $beforeAmount;//变动前额
//                        $record->balance = $afterAmount;//实时余额
//                        $record->status = 1;//交易成功
//                        $record->remark = '下级' . $data['type_cn'] . '消费奖励金币';//交易备注
//                        //添加资金流水
//                        RecordFacade::addRecord($record); //调用新增资金流水
//
//                        //添加贡献流水
//                        $inviteAward = new MemberInviteAward();
//                        $inviteAward->member_id = $tjrid;
//                        $inviteAward->from_member_id = $memberId; //贡献人
//                        $inviteAward->type = 1; //
//                        $inviteAward->gold = $gold; //金币
//                        $inviteAward->save();
//                    }
//                }
//            }
            //下级商务收益奖励
            if ($type == 34) {
                $config = Cache::get('SystemBasic'); //取平台配置
                $yield_rate = $config->yield_rate; //下级商务收益比例
                if ($yield_rate > 0) {
                    $memberInfo = MemberInfo::find($memberId); //会员
                    //奖励金币
                    $money = round($quantity * $yield_rate / 100, 2);
                    //查询推荐人
                    $tjrid = $memberInfo->pid;
                    $tjsAccount = MemberAccount::where('member_id', $tjrid)->first();
                    if (isset($tjsAccount)) {

                        $beforeAmount = $tjsAccount->surplus_rmb;
                        $afterAmount = $beforeAmount + $money;
                        //流水记录
                        $record = new MemberRecord();
                        $record->member_id = $tjsAccount->member_id;
                        $record->type = 37;//下级上午收益奖励
                        $record->account_type = 0; //账户类型
                        $record->amount = $money; //发生金额
                        $record->freeze_amount = 0;//冻结金额
                        $record->before_amount = $beforeAmount;//变动前额
                        $record->balance = $afterAmount;//实时余额
                        $record->status = 1;//交易成功
                        $record->remark = '下级商务收益奖励金额';//交易备注
                        //添加资金流水
                        RecordFacade::addRecord($record); //调用新增资金流水

                        //添加贡献流水
                        $inviteAward = new MemberInviteAward();
                        $inviteAward->member_id = $tjrid;
                        $inviteAward->from_member_id = $memberId; //贡献人
                        $inviteAward->type = 2; //
                        $inviteAward->money = $money; //金币
                        $inviteAward->save();
                    }
                }
            }

            //下级充值奖励
//            if ($type == 1 || $type == 35) {
//
//                $zstip = '';
//                if ($type == 35) {
//                    $zstip = '赠送部分';
//                }
//                $memberInfo = MemberInfo::find($memberId); //当前充值会员
//                $agentId = $memberInfo->agent_id; //所属代理商
//                //获取代理商提成比例
//                $config = Cache::get('SystemBasic'); //取平台配置
//                $invite_rate = $config->invite_rate; //直接下级充值奖励比例
//                $recordType = 21;
//                $recordRemark = '直接下级用户充值' . $zstip . '奖励金币';
//                if ($memberInfo->pid != $agentId) {
//                    $invite_rate = $config->consume_rate;//间接下级充值奖励比例
//                    $recordType = 38;
//                    $recordRemark = '间接下级用户充值' . $zstip . '奖励金币';
//                }
//                if ($agentId != 0 && $invite_rate > 0) {
//                    //赠送金币
//                    $gold = intval($quantity * $invite_rate / 100);
//                    //查询提成代理商
//                    $tjsAccount = MemberAccount::where('member_id', $agentId)->first();
//                    if (isset($tjsAccount)) {
//                        $beforeAmount = $tjsAccount->surplus_gold;
//                        $afterAmount = $beforeAmount + $gold;
//
//                        $record = new MemberRecord();
//                        $record->member_id = $tjsAccount->member_id;
//                        $record->type = $recordType;//
//                        $record->account_type = 1; //账户类型
//                        $record->amount = $gold; //发生金额
//                        $record->freeze_amount = 0;//冻结金额
//                        $record->before_amount = $beforeAmount;//变动前额
//                        $record->balance = $afterAmount;//实时余额
//                        $record->status = 1;//交易成功
//                        $record->remark = $recordRemark;//交易备注
//
//                        //调用新增资金流水
//                        RecordFacade::addRecord($record);
//                        //添加贡献流水
//                        $inviteAward = new MemberInviteAward();
//                        $inviteAward->member_id = $agentId;
//                        $inviteAward->from_member_id = $memberId; //贡献人
//                        $inviteAward->type = 0; //
//                        $inviteAward->gold = $gold; //金币
//                        $inviteAward->save();
//                    }
//                }
//            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            $this->exception($ex);
        }
    }

}
