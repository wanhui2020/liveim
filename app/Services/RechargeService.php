<?php

namespace App\Services;

use App\Facades\RecordFacade;
use App\Models\MemberAccount;
use App\Models\MemberDayCount;
use App\Models\MemberInfo;
use App\Models\MemberRecord;
use App\Models\SystemBasic;
use App\Traits\ResultTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

//充值服务
class RechargeService
{
    use ResultTrait;

    //充值审核处理
    public function rechargeDeal($rechargeModel, $accountType = 0,$is_lock = 0)
    {
        DB::beginTransaction();
        try {

            $type = $rechargeModel['type']; //变动类型
            $quantity = $rechargeModel['quantity']; //变动数量
            $memberId = $rechargeModel['member_id'];

            if ($rechargeModel['status'] == 1) {
                //通过（充值完成）
                if ($type == 2) {
                    //充值VIP
                    $vipDate = date('Y-m-d', strtotime('+' . $quantity . ' day'));
                    $account = MemberAccount::where('member_id', $memberId)->first();
                    $account['vip_expire_date'] = $vipDate;
                    $account->save();
                } else {
                    //充值金币
                    $sys_plus = 0;
                    $sys_minus = 0;
                    $memberInfo = MemberInfo::find($memberId); //充值的会员
                    /**
                     * 2020.1.6 后台手动增加的币不返给上级邀请人
                     *
                     * 如果充值成功
                     * 存在邀请人
                     * 并且该邀请人还有邀请人的权限
                     * 查询平台参数 直接下级充值奖励大于0
                     * 给邀请人金币钱包增加金币
                     * 添加流水
                     */
                    if ($rechargeModel['is_sys'] != 1){  //不是后台操作的金币才返回邀请人
                        if ($memberInfo->inviter_id != 0){ //存在邀请人
                            $inviInfo = MemberInfo::where('id',$memberInfo->inviter_id)->first();//邀请人
                            if ($inviInfo->is_inviter === 1){ //邀请人状态要为 1 被邀请人未完成自拍认证
                                $config = Cache::get('SystemBasic'); //取平台配置
                                if ($config) {
                                    $rate = $config->invite_rate;
                                } else {
                                    $rate = SystemBasic::first()->invite_rate;
                                }
                                if ($rate > 0) {
                                    $sharegold = round($quantity * ($rate/100)); //充值赠送的金币数量
                                    $inviterAccount = MemberAccount::where('member_id',$memberInfo->inviter_id)->first();
                                    $before_amount = $inviterAccount->surplus_gold;
                                    $inviterAccount->surplus_gold += $sharegold;

                                    //添加一条资金流水
                                    $record = new MemberRecord();
                                    $record->member_id = $memberInfo->inviter_id;
                                    $record->type = 103;//直接下级充值奖励
//                                $record->order_no = $rechargeModel['order_no']; //聊天订单通道编号
                                    $record->account_type = 1; //账户类型 金币
                                    $record->amount = $sharegold; //发生金额
                                    $record->freeze_amount = 0;//冻结金额
                                    $record->before_amount = $before_amount;//变动前额
                                    $record->balance = $inviterAccount->surplus_gold;//实时余额
                                    $record->status = 1;//交易成功
                                    $record->remark = '下级充值奖励';//交易备注
                                    $record->save();
                                    $inviterAccount->save();
                                }
                            }
                        }
                    }
                    $account = $memberInfo->account;
                    if ($type == 1 && $quantity > 0) {
                        //如果金币是减少则判断剩余数量是否足够
                        if ($accountType == 0 && $account->surplus_gold < $quantity) {
                            return $this->failure(1, '可提现金币余额少于减少的数量!');
                        }
                        if ($accountType == 1 && $account->notuse_gold < $quantity) {
                            return $this->failure(1, '不可提现金币余额少于减少的数量!');
                        }
                        $sys_minus = $quantity;
                        $quantity = -$quantity;

                    } else {
                        $sys_plus = $quantity;
                    }

                    if ($accountType == 0) {
                        $beforeAmount = $account->surplus_gold;
                        $account->surplus_gold += $quantity;
                        if ($is_lock == 0){ //判断是否可以提现  0 不可提现 1 可以提现  不可提现时增加锁定金币
                            $account->lock_gold += $quantity; //充值金币的时候增加锁定
                        }
//                        $vip_give = $rechargeModel['vip_give'] + $quantity;
//                        $account->notuse_gold = $vip_give;
                        $afterAmount = $account->surplus_gold;
                    } else {
                        //不可用账户
                        $beforeAmount = $account->notuse_gold;
                        $account->notuse_gold += $quantity;
                        $afterAmount = $account->notuse_gold;

                    }
                    $account->sys_plus += $sys_plus;
                    $account->sys_minus += $sys_minus;

                    //添加用户资金流水记录
                    $record = new MemberRecord();
                    $record->member_id = $memberId;
                    $record->order_no = $rechargeModel->order_no;
                    $type = $rechargeModel->type == 2 ? 22 : 1; //判断是充值金币还是购买VIP
                    if ($rechargeModel->is_sys == 1) {
                        $type = 8; //系统操作则是 后台管理资金
                    }
                    $record->type = $type;
                    $record->account_type = $accountType == 0 ? 1 : 2; //账户类型
                    $record->amount = $quantity; //发生金额
                    $record->freeze_amount = 0;//冻结金额
                    $record->before_amount = $beforeAmount;//变动前额
                    $record->balance = $afterAmount;//实时余额
                    $record->status = 1;//交易成功
                    $record->remark = $rechargeModel->remark;//交易备注
                    $record->created_at = date('Y-m-d H:i:s', strtotime('now'));
                    $record->save();
                    $giveGold = 0;
                    if ($rechargeModel['type'] == 0) {
                        //充值金币赠送
                        $giveRemark = '充值赠送金币';
                        if ($account->is_vip) {
                            $giveGold = $rechargeModel['vip_give'];
                            $giveRemark = 'VIP充值赠送金币';
                        } else {
                            $giveGold = $rechargeModel['give'];
                        }
                        if ($giveGold > 0) {
                            //赠送
                            $record = new MemberRecord();
                            $record->member_id = $memberId;
                            $record->type = 35;//充值赠送
                            $record->account_type = 1; //账户类型
                            $record->amount = $giveGold; //发生金额
                            $record->freeze_amount = 0;//冻结金额
                            $record->before_amount = $afterAmount;//变动前额
                            $record->balance = $afterAmount + $giveGold;//实时余额
                            $record->status = 1;//交易成功
                            $record->remark = $giveRemark;//交易备注
                            $record->created_at = date('Y-m-d H:i:s', strtotime('+1 second'));
                            $record->save();
                        }
                    }
                    $account->surplus_gold = $afterAmount + $giveGold;
                    if ($is_lock == 0){ //判断是否可以提现  0 不可提现 1 可以提现  不可提现时增加锁定金币
                        $account->lock_gold += $giveGold; //充值金币的时候增加锁定
                    }
                    $account->save();
                }
                //充值金额统计
                $dayInt = date('Ymd'); //日期
                $dayCount = MemberDayCount::firstOrNew(['member_id' => $memberId, 'dayint' => $dayInt]);
                if ($rechargeModel['amount'] > 0) {
                    $dayCount['rec_money'] += $rechargeModel['amount'];  //统计充值金额
                    $dayCount->save();
                }
            }

            $rechargeModel->save();
            DB::commit();

            return $this->succeed();

        } catch (\Exception $ex) {
            DB::rollBack();

            return $this->exception($ex);
        }
    }

}
