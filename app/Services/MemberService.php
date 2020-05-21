<?php

namespace App\Services;

use App\Facades\PushFacade;
use App\Facades\RecordFacade;
use App\Models\MemberAccount;
use App\Models\MemberCoatOrder;
use App\Models\MemberDayCount;
use App\Models\MemberExchange;
use App\Models\MemberExtend;
use App\Models\MemberFileView;
use App\Models\MemberGift;
use App\Models\MemberInfo;
use App\Models\MemberPlan;
use App\Models\MemberPlanOrder;
use App\Models\MemberPlanOrderContent;
use App\Models\MemberPlanOrderContentPic;
use App\Models\MemberPlanPic;
use App\Models\MemberRecord;
use App\Models\MemberReward;
use App\Models\MemberSignIn;
use App\Models\MemberTags;
use App\Models\MemberTakeNow;
use App\Models\MemberTalk;
use App\Models\SystemBasic;
use App\Traits\ResultTrait;
use App\Utils\Helper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

//会员相关服务实现类
class MemberService
{
    use ResultTrait;

    /*
     * 会员查看主播资源库方法
     * */
    public function viewFile(MemberFileView $model)
    {

        try {
            $id = $model['member_file_id']; //资源ID
            $viewMemberId = $model['member_id'];
            $memberId = $model['to_member_id']; //主播ID
            $type = $model['type']; //查看类型0:视频 1照片
            $viewMemberAccount = MemberAccount::where('member_id', $viewMemberId)->first(); //会员
            if ($viewMemberAccount->is_vip) {
                $viewModel = new MemberFileView();
                $viewModel->member_id = $viewMemberId;
                $viewModel->to_member_id = $memberId;
                $viewModel->member_file_id = $id;
                $viewModel->type = $type; //类型
                $viewModel->gold = 0; //vip 收费0
                $viewModel->save();
                //vip不扣费
                return $this->succeed();
            }
            DB::beginTransaction();
            //先判断该会员是否查看过该记录
            $viewCounts = MemberFileView::where(['member_id' => $viewMemberId, 'member_file_id' => $id])->count();
            $gold = 0;
            if ($viewCounts == 0) {
                //未查看过，要扣费。
                $memberExtend = MemberExtend::where('member_id', $memberId)->first(); //主播扩展
                $gold = $type == 0 ? $memberExtend->video_view_fee : $memberExtend->picture_view_fee; //取查看费用
                //2.判断查看会员余额是否足够
                if ($gold > 0) {
                    if ($viewMemberAccount->balance_gold < $gold) {
                        return $this->failure(1, '会员可用金币不足，不能查看主播资源库！');
                    }
                }
            }
            if ($gold > 0) {
                //1.会员扣除资费默认先扣可用余额
                $beforeAmount = $viewMemberAccount->surplus_gold;
                $afterAmount = $beforeAmount - $gold;

                $record = new MemberRecord();
                $record->member_id = $viewMemberId;
                $record->type = $type == 0 ? 15 : 14;//看视频/看颜照
                $record->account_type = 1; //账户类型
                $record->amount = -$gold; //发生金额
                $record->freeze_amount = 0;//冻结金额
                $record->before_amount = $beforeAmount;//变动前额
                $record->balance = $afterAmount;//实时余额
                $record->status = 1;//交易成功
                $record->remark = $type == 0 ? '看视频' : '看颜照';//交易备注

                //2.主播收益
                //获取平台基础数据设置，抽成比例
                $config = Cache::get('SystemBasic');
                if ($config != null && $config->rate > 0) {
                    $sxf = round($gold * ($config->rate / 100)); //手续费，四舍五入
                    $syGold = $gold - $sxf; //主播收益
                }
                $memberAccount = MemberAccount::where('member_id', $memberId)->first(); //主播账户

                $remark = '收益(' . $syGold . ') = ' . $gold . ' - 手续费(' . $sxf . ')';
                $beforeAmount = $memberAccount->surplus_gold;

                $zbRecord = new MemberRecord();
                $zbRecord->member_id = $memberId; //主播ID
                $zbRecord->type = $type == 0 ? 20 : 19;//视频被看收益/颜照被看收益
                $zbRecord->account_type = 1; //账户类型
                $zbRecord->amount = $syGold; //发生金额
                $zbRecord->freeze_amount = 0;//冻结金额
                $zbRecord->before_amount = $beforeAmount;//变动前额
                $zbRecord->balance = $beforeAmount + $syGold;//实时余额
                $zbRecord->status = 1;//交易成功
                $zbRecord->remark = $remark;//交易备注

                RecordFacade::addRecord($record, $zbRecord); //调用新增资金流水
            }
            $model['gold'] = $gold; //花费金币
            $model->save();

            DB::commit();
            return $this->succeed();

        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /*
     * 会员金币兑换
     * */
    public function goldExchange(MemberExchange $model)
    {
        DB::beginTransaction();
        try {
            $gold = $model['gold']; //兑换金币
            $memberId = $model['member_id'];
            //先判断会员
            $memberInfo = MemberInfo::find($memberId);
            if ($memberInfo == null || $memberInfo->status == 0) {
                return $this->failure(1, '会员不存在!');
            }
            $config = Cache::get('SystemBasic'); //取平台
            if ($gold < $config->dh_min) {
                return $this->failure(1, '最低兑换' . $config->dh_min . '币！');
            }
//            $candhGold = $memberInfo->account->surplus_gold; //可兑换金币
            if ($memberInfo->account->lock_gold < 0){
                $candhGold = $memberInfo->account->surplus_gold; //可兑换金币  减去锁定的金币
            }else{
                $candhGold = $memberInfo->account->surplus_gold - $memberInfo->account->lock_gold; //可兑换金币  减去锁定的金币
            }
//            if ($memberInfo->sex == 0) {
//                //会员，非主播
//                $candhGold = $memberInfo->account->cantx_gold; //可兑换金币
//            }
            if ($candhGold < $gold) {
                return $this->failure(1, '会员剩余可兑换金币不足！',[$candhGold,$gold]);
            }
            $dhrate = $config->dh_rate > 0 ? $config->dh_rate : 100;
            $rmb = round($gold * ($dhrate / 100) / 100, 2);
            $model['rmb'] = $rmb;
            //1.------保存记录---------
            $model->save();

            $memberAccount = $memberInfo->account; //会员账户

            //2.------资金流水处理------
            //1.会员先扣除可用余额
            $beforeAmount = $memberAccount->surplus_gold;
            $afterAmount = $beforeAmount - $gold;

            $record = new MemberRecord();
            $record->member_id = $memberId;
            $record->type = 3;//兑换
            $record->account_type = 1; //账户类型
            $record->amount = -$gold; //发生金额
            $record->freeze_amount = 0;//冻结金额
            $record->before_amount = $beforeAmount;//变动前额
            $record->balance = $afterAmount;//实时余额
            $record->status = 1;//交易成功
            $record->remark = '兑换资金';//交易备注
            //保存
            $record->save();

            //2.余额账号增加
            $beforeRmb = $memberAccount->surplus_rmb;
            $afterRmb = $beforeRmb + $rmb;

            $zbRecord = new MemberRecord();
            $zbRecord->member_id = $memberId; //主播ID
            $zbRecord->type = 3;//兑换
            $zbRecord->account_type = 0; //余额账户类型
            $zbRecord->amount = $rmb; //发生金额
            $zbRecord->freeze_amount = 0;//冻结金额
            $zbRecord->before_amount = $beforeRmb;//变动前额
            $zbRecord->balance = $afterRmb;//实时余额
            $zbRecord->status = 1;//交易成功
            $zbRecord->remark = '兑换资金';//交易备注
            //保存
            $zbRecord->save();

            //3.修改会员金币和余额
            $memberAccount->surplus_gold = $afterAmount;
            $memberAccount->surplus_rmb = $afterRmb;
            $memberAccount->save();

//           RecordFacade::addRecord($record, $zbRecord);

            DB::commit();
            return $this->succeed();

        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /*
     * 会员余额提现
     * */
    public function takeNow($data)
    {
        DB::beginTransaction();
        try {
            //1.先判断会员是否可用
            $memberId = $data['member_id'];
            $amount = $data['amount']; //提现金额
            $memberInfo = MemberInfo::find($memberId);
            if ($memberInfo == null || $memberInfo->status == 0) {
                DB::rollBack();
                return $this->failure(1, '会员不存在!');
            }
            //2.判断会员是否已进行了实名认证
            if ($memberInfo->realname_check == 0) {
                DB::rollBack();
                return $this->failure(1, '会员未进行实名实证，不能提现!');
            }
            $config = Cache::get('SystemBasic'); //取平台参数
            if ($amount < $config->tx_min) {
                DB::rollBack();
                return $this->failure(1, '单笔最低提现' . $config->tx_min . '元！');
            }
            $cantxRmb = $memberInfo->account->cantx_rmb; //剩余可提现余额
            if ($cantxRmb < $amount) {
                DB::rollBack();
                return $this->failure(1, '剩余可提现余额不足！');
            }
            //3.判断提现是否收取手续费
            //获取今日提现次数
            $start_time = Carbon::now()->startOfDay();
            $end_time = Carbon::now()->endOfDay();
//            $ytxlist = MemberTakeNow::where(['status' => 1, 'member_id' => $memberId])->whereBetween('deal_time', [$start_time, $end_time]);
            $ytxlist = MemberTakeNow::where(['member_id' => $memberId])->whereBetween('created_at', [$start_time, $end_time]);
            $ytxCount = $ytxlist->count();
            if ($ytxCount >= $config->tx_max_count) {
                DB::rollBack();
                return $this->failure(1, '当日提现次数已用完！');
            }
            if ($ytxCount > 0) {
                //已提现过，判断提现额度是否超限
                $ytxAmount = $ytxlist->sum('amount');
                $syktx = $config->tx_max_amount - $ytxAmount;
                if ($syktx < 0) {
                    DB::rollBack();
                    return $this->failure(1, '当日最多还可提现' . $syktx . '元！');
                }
            }
            //判断手续费
            $fee = 0;
            if ($ytxCount >= $config->tx_nofee_count) {
                //超过免手续费笔数,收取手续费
                $fee = $amount * $config->tx_rate / 100;
            }
            $data['order_no'] = Helper::getNo('tx');
            $data['fee_money'] = $fee;
            $data['real_amount'] = $amount - $fee; //实际到账金额
            /**
             * 用户发起申请提现之后
             * 将用户的钱包进行冻结
             */
            $memberAcc = MemberAccount::where('member_id',$data['member_id'])->lockForUpdate()->first();
            if (!isset($memberAcc)){
                DB::rollBack();
                return $this->failure('用户钱包不存在，请联系管理员!');
            }
            $this->logs('申请提现日志',[$memberInfo->nick_name,$amount,Carbon::now()]);
            $memberAcc->notuse_rmb += $data['amount'];
            $memberAcc->save();
            $data->save();
            DB::commit();
            return $this->succeed();

        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }


    /*
     * 会员赠送礼物
     * */
    public function giveGift(MemberGift $data, $gift_url = '')
    {
        DB::beginTransaction();
        try {
            //1.先判断会员是否可用
            $memberId = $data['member_id'];
            $gold = $data['gold']; //使用金币
            $toMemberId = $data['to_member_id']; //接收主播
            $memberInfo = MemberInfo::find($memberId);
            $memberAccount = $memberInfo->account; //会员账户
            if ($memberAccount->balance_gold < $gold) {
                return $this->failure(1, '会员可用金币不足，不能赠送该礼物！');
            }
            $zbInfo = MemberInfo::find($toMemberId);
            if ($gold != 0) {

                $memberExtend = $zbInfo->extend; //主播扩展
                //1.会员扣除资费默认先扣可用余额
                $beforeAmount = $memberAccount->surplus_gold;
                $afterAmount = $beforeAmount - $gold;

                $record = new MemberRecord();
                $record->member_id = $memberId;
                $record->type = 2;//送礼物
                $record->account_type = 1; //账户类型
                $record->amount = -$gold; //发生金额
                $record->freeze_amount = 0;//冻结金额
                $record->before_amount = $beforeAmount;//变动前额
                $record->balance = $afterAmount;//实时余额
                $record->status = 1;//交易成功
                $record->remark = '送礼物';//交易备注

                //2.主播收益
                //获取平台基础数据设置，抽成比例
                $rate = $memberExtend->gift_rate; //默认取主播自身的，如果未单独设置，则取平台默认值
                if ($rate == 0) {
                    $config = Cache::get('SystemBasic'); //取平台
                    $rate = $config->rate;
                }
                $sxf = round($gold * ($rate / 100)); //手续费，四舍五入
                $syGold = $gold - $sxf; //主播收益

                $zhuboAccount = $zbInfo->account; //主播账户
                $remark = '收益(' . $syGold . ')=' . $gold . ' - 手续费(' . $sxf . ')';
                $beforeAmount = $zhuboAccount->surplus_gold;

                $zbRecord = new MemberRecord();
                $zbRecord->member_id = $toMemberId; //主播ID
                $zbRecord->type = 9;//收到礼物
                $zbRecord->account_type = 1; //账户类型
                $zbRecord->amount = $syGold; //发生金额
                $zbRecord->freeze_amount = 0;//冻结金额
                $zbRecord->before_amount = $beforeAmount;//变动前额
                $zbRecord->balance = $beforeAmount + $syGold;//实时余额
                $zbRecord->status = 1;//交易成功
                $zbRecord->remark = $remark;//交易备注
                /**
                 * 如果主播有经纪人
                 * 并且该经纪人还有邀请经纪人权限
                 * 主播收到礼物要返给
                 * 经纪人可提现金币
                 */
                if ($zbInfo->inviter_zbid != 0){ //存在经纪人
                    $jjrInfo = MemberInfo::where('id',$zbInfo->inviter_zbid)->first();
                    if ($jjrInfo->is_inviter_zb === 1){ //有邀请经纪人权限
                        $config = Cache::get('SystemBasic'); //取平台配置
                        if ($config) {
                            $rate = $config->invite_gift_rate;
                        } else {
                            $rate = SystemBasic::first()->invite_gift_rate;
                        }
                        if ($rate > 0 ){
                            $invitergold = round($syGold * ($rate/100)); //主播收礼物分给经纪人
                            if ($invitergold > 0 ){
                                $inviterAccount = MemberAccount::where('member_id',$zbInfo->inviter_zbid)->first();
                                $beforeAmount = $inviterAccount->surplus_gold;
                                $inviterAccount->surplus_gold += $invitergold;    //经纪人可体现金币
                                $inviterAccount->save();

                                $inviterRecord = new MemberRecord();
                                $inviterRecord->member_id = $zbInfo->inviter_zbid; //经纪人id
                                $inviterRecord->type = 101;//类型
                                $inviterRecord->account_type = 1; //账户类型
                                $inviterRecord->amount = $invitergold; //发生金额
                                $inviterRecord->freeze_amount = 0;//冻结金额
                                $inviterRecord->before_amount = $beforeAmount;//变动前额
                                $inviterRecord->balance = $inviterAccount->surplus_gold;//实时余额
                                $inviterRecord->status = 1;//交易成功
                                $inviterRecord->remark = '主播收礼物收益分成';//交易备注
                                $inviterRecord->save();
                            }
                        }
                    }
                }
                RecordFacade::addRecord($record, $zbRecord); //新增资金流水
            }
            //记录保存
            $data->save();

            DB::commit();

//            //发送礼物成功后，推送
//            $key = 'gift';
//            //发送通知
//            $param = array(
//                'key' => $key,
//                'giftid' => $data['id'], //礼物ID
//                'url' => $gift_url, //礼物地址
//                'nick_name' => $memberInfo['nick_name'],  //会员昵称
//                'pic' => $memberInfo['head_pic'], //会员头像
//            );
//            ImFacade::addRoom((string)$toMemberId, $param);

            return $this->succeed();

        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /*
     * 会员打赏主播
     * */
    public function reward(MemberReward $data)
    {
        DB::beginTransaction();
        try {
            //1.先判断会员是否可用
            $memberId = $data['member_id'];
            $gold = $data['gold']; //使用金币
            $toMemberId = $data['to_member_id']; //接收主播
            $memberAccount = MemberAccount::where('member_id', $memberId)->first(); //会员
            if ($memberAccount->balance_gold < $gold) {
                return $this->failure(1, '会员打赏金币超出剩余可用金币！');
            }
            if ($gold != 0) {

                $zbInfo = MemberInfo::find($toMemberId);
                $memberExtend = $zbInfo->extend; //主播扩展

                //1.会员扣除资费默认先扣可用余额
                $beforeAmount = $memberAccount->surplus_gold;
                $afterAmount = $beforeAmount - $gold;

                $record = new MemberRecord();
                $record->member_id = $memberId;
                $record->type = 32;//打赏
                $record->account_type = 1; //账户类型
                $record->amount = -$gold; //发生金额
                $record->freeze_amount = 0;//冻结金额
                $record->before_amount = $beforeAmount;//变动前额
                $record->balance = $afterAmount;//实时余额
                $record->status = 1;//交易成功
                $record->remark = '打赏';//交易备注

                //2.主播收益
                //获取平台基础数据设置，抽成比例
                $rate = $memberExtend->other_rate; //默认取主播自身的，如果未单独设置，则取平台默认值
                if ($rate == 0) {
                    $config = Cache::get('SystemBasic'); //取平台
                    $rate = $config->ds_rate;
                }
                $sxf = round($gold * ($rate / 100)); //手续费，四舍五入
                $syGold = $gold - $sxf; //主播收益

                $zhuboAccount = $zbInfo->account; //主播账户
                $remark = '收益(' . $syGold . ')=' . $gold . ' - 手续费(' . $sxf . ')';
                $beforeAmount = $zhuboAccount->surplus_gold;

                $zbRecord = new MemberRecord();
                $zbRecord->member_id = $toMemberId; //主播ID
                $zbRecord->type = 33;//收到礼物
                $zbRecord->account_type = 1; //账户类型
                $zbRecord->amount = $syGold; //发生金额
                $zbRecord->freeze_amount = 0;//冻结金额
                $zbRecord->before_amount = $beforeAmount;//变动前额
                $zbRecord->balance = $beforeAmount + $syGold;//实时余额
                $zbRecord->status = 1;//交易成功
                $zbRecord->remark = $remark;//交易备注

                RecordFacade::addRecord($record, $zbRecord); //新增资金流水
            }
            //记录保存
            $data->save();

            DB::commit();
            return $this->succeed();

        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }


    /*
     * 会员补签
     * */
    public function buqian(MemberSignIn $data)
    {
        try {

            $memberId = $data['member_id'];
            //判断是否设置了补签基础扣费
            $config = Cache::get('SystemBasic');
            $memberAccount = MemberAccount::where('member_id', $memberId)->first();
            $bqCount = $memberAccount->bq_count; //补签次数
            $bqAmount = $config->bk_kf + 10 * $bqCount; //补签扣费
            if ($memberAccount->balance_gold < $bqAmount) {
                return $this->failure(1, '会员可用金币不足，不能补签！');
            }
            if ($bqAmount > 0) {

                $beforeAmount = $memberAccount->surplus_gold;
                $afterAmount = $beforeAmount - $bqAmount;

                $record = new MemberRecord();
                $record->member_id = $memberId;
                $record->type = 7;//补签
                $record->account_type = 1; //账户类型（金币账户）
                $record->amount = -$bqAmount; //发生金额
                $record->freeze_amount = 0;//冻结金额
                $record->before_amount = $beforeAmount;//变动前额
                $record->balance = $afterAmount;//实时余额
                $record->status = 1;//交易成功
                $record->remark = '补签扣费';//交易备注

                RecordFacade::addRecord($record); //调用新增资金流水
            }

            //记录保存
            $data->save();

            DB::commit();
            return $this->succeed();
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /*
     * 会员申请主播换衣
     * */
    public function addCoatOrder(MemberCoatOrder $data)
    {
        try {
            $id = $data['member_coat_id']; //衣服ID
            $memberId = $data['to_member_id']; //衣服所属主播ID
            $viewMemberId = $data['member_id'];//查看会员ID
            $gold = $data['gold'];//金币
            //先判断该会员是否有未结束的换衣订单
            $viewCounts = MemberCoatOrder::where(['member_id' => $viewMemberId, 'member_coat_id' => $id])->where('status', '<', 3)->count();
            if ($viewCounts > 0) {
                return $this->failure(1, '会员还有未结束的换衣订单，不能再提交！');
            }
            //判断主播是否有未结束的换衣订单
            $counts = MemberCoatOrder::where(['to_member_id' => $memberId])->where('status', '<', 3)->count();
            if ($counts > 0) {
                return $this->failure(1, '主播还有未结束的换衣订单，请更换其他主播！');
            }
            $data = new MemberCoatOrder();
            $data['member_id'] = $viewMemberId;
            $data['to_member_id'] = $memberId;
            $data['member_coat_id'] = $id;
            $data['gold'] = $gold;
            $data['status'] = 0; //申请中

            //记录保存
            $data->save();

            DB::commit();
            return $this->succeed();
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /*
     * 换衣订单处理方式
     * */
    public function dealCoatOrder($id, $dealStatus)
    {
        try {
            $data = MemberCoatOrder::find($id); //订单对象
            if (!isset($data)) {
                return $this->failure(1, '换衣订单不存在！');
            }
            $memberId = $data['member_id']; //发起会员
            $toMemberId = $data['to_member_id']; //接收主播
            $gold = $data['gold']; //消费金币
            if ($dealStatus == 1) {
                $data['status'] = $dealStatus; //修改处理状态
                if ($gold > 0) {
                    $config = Cache::get('SystemBasic'); //取平台配置
                    $rate = $config->hy_rate; //换衣收成占比
                    //接受换衣，扣费
                    $memberAccount = MemberAccount::where('member_id', $memberId)->first(); //会员
                    $zhuboAccount = MemberAccount::where('member_id', $toMemberId)->first(); //主播账户
                    //1.会员扣除资费默认先扣可用余额
                    $beforeAmount = $memberAccount->surplus_gold;
                    $afterAmount = $beforeAmount - $gold;

                    $record = new MemberRecord();
                    $record->member_id = $memberId;
                    $record->type = 28;//付费换衣服
                    $record->account_type = 1; //账户类型
                    $record->amount = -$gold; //发生金额
                    $record->freeze_amount = 0;//冻结金额
                    $record->before_amount = $beforeAmount;//变动前额
                    $record->balance = $afterAmount;//实时余额
                    $record->status = 1;//交易成功
                    $record->remark = '付费换衣服';//交易备注

                    //2.主播收益
                    $sxf = round($gold * ($rate / 100)); //手续费，四舍五入
                    $syGold = $gold - $sxf; //主播收益
                    $beforeAmount = $zhuboAccount->surplus_gold;
                    $remark = '收益(' . $syGold . ')=' . $gold . ' - 手续费(' . $sxf . ')';

                    $zbRecord = new MemberRecord();
                    $zbRecord->member_id = $toMemberId; //主播ID
                    $zbRecord->type = 29;//换衣收益
                    $zbRecord->account_type = 1; //账户类型
                    $zbRecord->amount = $syGold; //发生金额
                    $zbRecord->freeze_amount = 0;//冻结金额
                    $zbRecord->before_amount = $beforeAmount;//变动前额
                    $zbRecord->balance = $beforeAmount + $syGold;//实时余额
                    $zbRecord->status = 1;//交易成功
                    $zbRecord->remark = $remark;//交易备注

                    RecordFacade::addRecord($record, $zbRecord);
                }

            } else {
                if ($dealStatus == 2 && $data['status'] != 1) {
                    //已完成订单，必须是换中的订单
                    return $this->failure(1, '换衣订单不能进行完成操作！');
                }
                if ($dealStatus == 3 && $data['status'] != 0) {
                    //取消订单
                    return $this->failure(1, '换衣订单已不能取消！');
                }
                //结束或拒接订单
                $data['status'] = $dealStatus; //修改处理状态
            }

            $data->save();
            DB::commit();
            return $this->succeed();
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }


    /*
     * 会员发起通话订单
     * */
    public function addTalkOrder(MemberTalk $data)
    {
        DB::beginTransaction();
        try {
            $memberId = $data['member_id']; //发起会员
            $toMemberId = $data['to_member_id']; //接收主播
            $type = $data['type']; //类型（0.文本 1.语音 2.视频）
            $zbInfo = MemberInfo::find($toMemberId); //查询主播
            if ($type != 0) {
                //如果是语音视频要先判断主播是否空闲
                if ($zbInfo->online_status == 0) {
                    DB::rollBack();
                    return $this->failure(1, '对不起，该主播不在线！');
                }
                if ($zbInfo->vv_busy == 1) {
                    DB::rollBack();
                    return $this->failure(1, '对不起，该主播正在通话中！');
                }
            }
            //查询主播收费
            $zbExtend = $zbInfo->extend; //主播收费
            if ($type == 0) {
                $price = $zbExtend->text_fee; //通道消息收费
            } else if ($type == 1) {
                $price = $zbExtend->voice_fee; //语音通话收费
            } else {
                $price = $zbExtend->video_fee; //视频收费
            }
            //判断会员是否可发起通话
            $memberInfo = MemberInfo::find($memberId);
            $memberAccount = $memberInfo->account; // MemberAccount::where('member_id', $memberId)->first(); //会员
//            if ($memberAccount->surplus_gold < $price && $memberAccount->notuse_gold < $price) {
//                return $this->failure(1, '会员剩余金币不足，不能发起聊天！');
//            }
            if ($memberAccount->balance_gold < $price || $memberAccount->balance_gold < 0) {
                DB::rollBack();
                return $this->failure(1, '会员可用金币不足，不能发起聊天！');
            }
            $data['price'] = $price;
            $data['amount'] = $price;

            if ($type == 0) {
//                if ($zbInfo->sex === 1 && $zbInfo->selfie_check === 0){
//                    /**
//                     * 女性用户未进行自拍认证不能发送消息
//                     */
//                    return $this->validation('请先进行自拍认证！');
//                }
                //如果是文本聊天，直接处理
                $data['status'] = 2; //直接结束
                $data['times'] = 1; //条
                $data['begin_time'] = Helper::getNowTime();
                $data['end_time'] = Helper::getNowTime();

                //结束文本消息，直接扣款
                $config = Cache::get('SystemBasic'); //取平台配置
                //判断文本消息达到免费条数
                $freeCount = $config->free_text; //免费笔数
                $yfcount = MemberTalk::where(['member_id' => $memberId, 'to_member_id' => $toMemberId, 'type' => 0])->count(); //已发条数
                if ($yfcount > $freeCount) {
                    //超出才收费
                    $rate = $zbExtend->talk_rate; //默认取主播自身的，如果未单独设置，则取平台默认值，取费率
                    if ($rate == 0) {
                        $rate = $config->rate;
                    }
                    //1.会员扣除资费默认先扣可用余额
                    $beforeAmount = $memberAccount->surplus_gold;
                    $afterAmount = $beforeAmount - $price;
                    //2.添加记录
                    $record = new MemberRecord();
                    $record->member_id = $memberId;
                    $record->type = 11;//11.普通消息消费 12.语音消费 13.视频消费
                    $record->account_type = 1; //账户类型
                    $record->amount = -$price; //发生金额
                    $record->freeze_amount = 0;//冻结金额
                    $record->before_amount = $beforeAmount;//变动前额
                    $record->balance = $afterAmount;//实时余额
                    $record->status = 1;//交易成功
                    $record->remark = '文本消息收费';//交易备注

                    //2.主播收益
                    $zhuboAccount = $zbInfo->account;
                    $sxf = round($price * ($rate / 100)); //手续费，四舍五入
                    $syGold = $price - $sxf; //主播收益
                    $beforeAmount = $zhuboAccount->surplus_gold;

                    $zbRecord = new MemberRecord();
                    $zbRecord->member_id = $toMemberId; //主播ID
                    $zbRecord->type = 16;//类型
                    $zbRecord->account_type = 1; //账户类型
                    $zbRecord->amount = $syGold; //发生金额
                    $zbRecord->freeze_amount = 0;//冻结金额
                    $zbRecord->before_amount = $beforeAmount;//变动前额
                    $zbRecord->balance = $beforeAmount + $syGold;//实时余额
                    $zbRecord->status = 1;//交易成功
                    $zbRecord->remark = '文本消息收益';//交易备注

                    $data['amount'] = $price;
                    $data['total_profit'] = $syGold;

                    RecordFacade::addRecord($record, $zbRecord); //调用新增资金流水
                }
            } else {
                //余额不足两分钟，不能发起通话
                if ($price > 0 && $memberAccount->balance_gold <= $price * 2) {
                    DB::rollBack();
                    return $this->failure(1, '会员金币不够通话2分钟，不能发起聊天！');
                }
            }

            //记录保存
            $data->save();
            //发起通话之后 首先将发起方 变成忙碌
//            $memberInfo->vv_busy = 1;
            $memberInfo->save();
            $ret = array(
                'roomid' => $data['id']
            );

//            if ($type != 0) {
//                $key = $type == 1 ? 'voice' : 'video';
//                //发送通知
//                $param = array(
//                    'key' => $key,
//                    'roomid' => $data['id'],
//                    'nick_name' => $memberInfo['nick_name'],
//                    'pic' => $memberInfo['head_pic']
//                );
//                ImFacade::addRoom((string)$toMemberId, $param);
//            }
            DB::commit();

            return $this->succeed($ret);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }


    /*
     * 会员通话订单操作处理
     * */
    public function dealTalkOrder($data, $dealStatus)
    {
        DB::beginTransaction();
        try {

            $data = MemberTalk::where('id',$data['id'])->lockForUpdate()->first();
            if (!isset($data)) {
                DB::rollBack();
                return $this->failure(1, '对不起，未找到处理通话订单！');
            }
            if ($dealStatus == 3 && $data->status != 1) {
                DB::rollBack();
                return $this->failure(1, '处理状态错误,该订单没有正在聊天！',$data);
            }

            $memberId = $data->member_id; //发起会员
            $toMemberId = $data->to_member_id; //接收主播
            $type = $data->type; //类型（0.文本 1.语音 2.视频）
            $gold = $data->price;

            $memberInfo = MemberInfo::find($memberId); //会员
//            $memberInfo->vv_busy = 0 ;
//            $memberInfo->save() ;
            $memberAccount = $memberInfo->account; //会员
            if (!isset($memberAccount)){
                DB::rollBack();
                return $this->validation('会员账户异常,请联系管理员！');
            }
            $zhuboInfo = MemberInfo::find($toMemberId);//主播账户

            if ($dealStatus == 1) {
                //接听//开始聊天
                $data->status = 1; //开始
                $data->begin_time = Helper::getNowTime();

                //计算主播1分钟收益
                $zhuboExtend = $zhuboInfo->extend;
                $rate = $zhuboExtend->talk_rate; //默认取主播自身的，如果未单独设置，则取平台默认值，取费率
                if ($rate == 0) {
                    $config = Cache::get('SystemBasic');
                    $rate = $config->rate;
                }
                $sxf = round($gold * ($rate / 100)); //手续费，四舍五入
                $syGold = $gold - $sxf; //主播收益
                $data->profit = $syGold;
                $data->total_profit = $syGold;
                if ($data->begin_time === Helper::getNowTime()){
                    if ($memberAccount->notuse_gold > 0){
                        $memberAccount->notuse_gold = 0;
                    }
                    //冻结会员账户
                    $memberAccount->notuse_gold += $gold; //冻结1分钟
                    $memberAccount->save();
                }else{
                    //冻结会员账户
                    $memberAccount->notuse_gold += $gold; //冻结1分钟
                    $memberAccount->save();
                }
                //主播状态修改
                $zhuboInfo->vv_busy = 1; //忙碌
                $zhuboInfo->save();
                //用户状态修改
                $memberInfo->vv_busy = 1 ;//忙碌
                $memberInfo->save() ;
            }
            else if ($dealStatus == 3) {
                //结束聊天，进行结算
                $nowTime = date("Y-m-d H:i:s", time());
                $totalS = strtotime($nowTime) - strtotime($data->begin_time); //总秒数
                $totalM = ceil($totalS / 60); //聊天总分钟数
                $totalAmount = $data->price * $totalM; //总共消费
                $syGold = 0;
                $memberInfo->vv_busy = 0 ;
                $memberInfo->save() ;
                if ($totalAmount > 0) {
//                    Log::info('调用扣费生成流水',[$data]);
                    //如果有消费才增加流水记录
                    //1.会员扣除资费默认先扣可用余额
                    $beforeAmount = $memberAccount->surplus_gold;
                    $afterAmount = $beforeAmount - $totalAmount;

                    $record = new MemberRecord();
                    $record->member_id = $memberId;
                    $record->talk_id = $data->id; //聊天订单id
                    $record->type = $type == 1 ? 12 : 13;//11.普通消息消费 12.语音消费 13.视频消费
                    $record->account_type = 1; //账户类型
                    $record->amount = -$totalAmount; //发生金额
                    $record->freeze_amount = 0;//冻结金额
                    $record->before_amount = $beforeAmount;//变动前额
                    $record->balance = $afterAmount;//实时余额
                    $record->status = 1;//交易成功
                    $record->remark = $type == 1 ? '语音通话消费' : '视频通话消费';//交易备注

                    //2.主播收益
                    //获取费率
                    $config = Cache::get('SystemBasic'); //取平台配置
                    $zhuboExtend = $zhuboInfo->extend; //主播配置
                    $zhuboAccount = $zhuboInfo->account; //主播账户
                    $rate = $zhuboExtend->talk_rate; //默认取主播自身的，如果未单独设置，则取平台默认值，取费率
                    if ($rate == 0) {
                        $rate = $config->rate;
                    }

                    $sxf = round($totalAmount * ($rate / 100)); //手续费，四舍五入
                    $syGold = $totalAmount - $sxf; //主播收益

                    $beforeAmount = $zhuboAccount->surplus_gold;
                    $afterAmount = $beforeAmount + $syGold;

                    $zbRecord = new MemberRecord();
                    $zbRecord->member_id = $toMemberId; //主播ID
                    $record->talk_id = $data->id; //聊天订单id
                    $zbRecord->type = $type == 1 ? 17 : 18;//类型
                    $zbRecord->account_type = 1; //账户类型
                    $zbRecord->amount = $syGold; //发生金额
                    $zbRecord->freeze_amount = 0;//冻结金额
                    $zbRecord->before_amount = $beforeAmount;//变动前额
                    $zbRecord->balance = $afterAmount;//实时余额
                    $zbRecord->status = 1;//交易成功
                    $zbRecord->remark = $type == 1 ? '语音通话收益' : '视频通话收益';//交易备注
                    $talk = 1;
                    /**
                     * 如果存在经纪人
                     * 并且该经纪人的经纪人权限还在
                     *
                     */
                    if ($zhuboInfo->inviter_zbid != 0){ //如果存在经纪人
                        $inviInfo = MemberInfo::where('id',$zhuboInfo->inviter_zbid)->first();
                        if ($inviInfo->is_inviter_zb ===1){
                            $config = Cache::get('SystemBasic'); //取平台配置
                            if ($config) {
                                $rate = $config->invite_gift_rate;
                            } else {
                                $rate = SystemBasic::first()->invite_gift_rate;
                            }
                            if ($rate > 0 ){
                                $invitergold = round($syGold * ($rate/100)); //主播通话收益返给经纪人
                                if ($invitergold > 0){
                                    $inviterAccount = MemberAccount::where('member_id',$zhuboInfo->inviter_zbid)->first();
                                    $beforeAmount = $inviterAccount->surplus_gold;
                                    $inviterAccount->surplus_gold += $invitergold;    //经纪人可体现金币
                                    $inviterAccount->save();

                                    $inviterRecord = new MemberRecord();
                                    $inviterRecord->member_id = $zhuboInfo->inviter_zbid; //经纪人id
                                    $inviterRecord->order_no = $data->channel_code; //聊天订单通道编号
                                    $inviterRecord->type = 102;//类型
                                    $inviterRecord->account_type = 1; //账户类型
                                    $inviterRecord->amount = $invitergold; //发生金额
                                    $inviterRecord->freeze_amount = 0;//冻结金额
                                    $inviterRecord->before_amount = $beforeAmount;//变动前额
                                    $inviterRecord->balance = $inviterAccount->surplus_gold;//实时余额
                                    $inviterRecord->status = 1;//交易成功
                                    $inviterRecord->remark = $type == 1 ? '主播语音通话收益分成' : '主播视频通话收益分成';//交易备注
                                    $inviterRecord->save();
                                }
                            }
                        }
                    }
                    RecordFacade::addRecord($record, $zbRecord, true,$talk); //调用新增资金流水
                }

                $data->amount = $totalAmount;
                $data->times = $totalS; //总秒数
                $data->end_time = $nowTime; //结束日期
                $data->status = 2; //已结束
                $data->total_profit = $syGold; //总收益

                //主播改为空闲
                $zhuboInfo->vv_busy = 0; //空闲
                $zhuboInfo->save();
                //用户改为空闲
                $memberInfo->vv_busy = 0 ;//空闲
                $memberInfo->save() ;
            }
            else {
                $data->status = 2; //拒绝，直接结束
                $data->amount = 0; //未消费
                $memberInfo->vv_busy = 0 ;
                $memberInfo->save() ;
                //主播状态修改
                $zhuboInfo->vv_busy = 0; //忙碌
                $zhuboInfo->save();
//                if ($data->status == 1) {
//                    return $this->failure(1, '处理状态错误！无dealstatus');
//                }
            }
            //记录修改保存
            $data->save();

            DB::commit();
            //发送通知
//            if ($data['status'] == 2) {
//                //发送通知
//                $param = array(
//                    'key' => 'over'
//                );
//                ImFacade::addRoom((string)$toMemberId, $param);
//                ImFacade::addRoom((string)$memberId, $param);
//            }

            return $this->succeed();
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }


    /*
    * 创建主播商务服务计划
    * */
    public function addPlan($data, $piclist)
    {
        DB::beginTransaction();
        try {


            $model = new MemberPlan();
            $model->fill($data);
            //保存订单信息
            $model->save();

            //订单详细内容
            if (isset($piclist)) {
                $picAray = explode(',', $piclist);
                foreach ($picAray as $pic) {
                    $picModel = new MemberPlanPic();
                    $picModel->plan_id = $model->id;
                    $picModel->pic = $pic;
                    $picModel->save();
                }
            }
            DB::commit();
            return $this->succeed();
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }


    /*
     * 创建会员商务邀约订单
     * */
    public function addPlanOrder($data, $proidstr)
    {
        DB::beginTransaction();
        try {

            $date = $data['service_date'];
            $amount = $data['amount']; //金额

            //判断主播当天是否已经有订单
            $count = MemberPlanOrder::where(['to_member_id' => $data['to_member_id'], 'service_date' => $date])->whereIn('status', [0, 1, 2, 4, 5, 6])->count();
            if ($count > 0) {
                DB::rollBack();
                return $this->failure(1, '该主播[' . $date . ']已有服务订单！');
            }
            //判断预约日期是否超出1周
            $datetime_start = date_create($date);
            $datetime_end = date_create(date('Y-m-d'));
            $days = date_diff($datetime_start, $datetime_end)->days;
            if ($days > 7) {
                DB::rollBack();
                return $this->failure(1, '商务最多预约一周以内的日期！');
            }

            //计算该单主播收益
            $config = Cache::get('SystemBasic'); //取平台配置
            $business_rate = $config->business_rate; //商务平台占比
            if ($business_rate > 0) {
                $profit = round($amount * (1 - $business_rate / 100), 2);//四舍五入保留2位小数
                $data['profit'] = $profit;
            }

            $model = new MemberPlanOrder();
            $model->fill($data);
            //保存订单信息
            $model->save();

            //订单详细内容
            $proidAray = explode(',', $proidstr);

            foreach ($proidAray as $proid) {
                $pro = MemberPlan::find($proid);
                $orderContent = new MemberPlanOrderContent();
                $orderContent->order_id = $model->id;
                $orderContent->project = $pro['project'];
                $orderContent->content = $pro['content'];
                $orderContent->sort = $pro['sort'];
                $orderContent->save();
                $contentid = $orderContent->id;

                //查询图片
                $pics = MemberPlanPic::where('plan_id', $proid)->get(['pic']);
                foreach ($pics as $pic) {
                    $contentPic = new MemberPlanOrderContentPic();
                    $contentPic->plan_id = $contentid;
                    $contentPic->pic = $pic['pic'];
                    $contentPic->save();
                }
            }

            DB::commit();
            return $this->succeed();
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }


    /*
     * 会员商务邀约订单操作处理
     *
     * */
    public function dealPlanOrder($data, $dealStatus, $cancel_type = 0)
    {
        DB::beginTransaction();
        try {
            $toMemberId = $data['to_member_id']; //接收主播
            if ($dealStatus == 1) {
                //完成支付
                $data['pay_status'] = 1; //已支付
                $data['status'] = 1; // 待接单
            } else if ($dealStatus == 2) {
                //接单
                $data['status'] = 2;
            } else if ($dealStatus == 3) {
                //拒单
                $data['pay_status'] = 3; //待退款
                $data['status'] = 3;
            } else if ($dealStatus == 4) {
                //开始服务
                $data['status'] = 4;
            } else if ($dealStatus == 5) {
                //结束服务
                $data['status'] = 5;
            } else if ($dealStatus == 7) {
                //拒单
                $data['pay_status'] = 4; //待退款
                $data['status'] = 7;
            } else if ($dealStatus == 9) {
                //取消订单
                $data['pay_status'] = 2; //未支付
                $data['status'] = 9;
            } else {
                //结算操作
                $data['status'] = 6;
            }
            if ($dealStatus == 6) {
                //结算
                $profit = $data['profit']; //主播收益
                $zhuboAccount = MemberAccount::where('member_id', $toMemberId)->first(); //主播账户
                //1.会员扣除资费默认先扣可用余额
                $beforeAmount = $zhuboAccount->surplus_rmb; //可用余额
                $afterAmount = $beforeAmount + $profit;

                $record = new MemberRecord();
                $record->member_id = $toMemberId;
                $record->type = 34;//商务邀约收益
                $record->account_type = 0; //余额
                $record->amount = $profit; //发生金额
                $record->freeze_amount = 0;//冻结金额
                $record->before_amount = $beforeAmount;//变动前额
                $record->balance = $afterAmount;//实时余额
                $record->status = 1;//交易成功
                $record->remark = '商务邀约收益';//交易备注

                RecordFacade::addRecord($record);
            }
            //记录修改保存
            $data['cancel_type'] = $cancel_type;
            $data->save();

            //充值成功
            if ($dealStatus == 1) {
                //充值进入日统计
                $dayInt = date('Ymd'); //日期
                $dayCount = MemberDayCount::firstOrNew(['member_id' => $data['member_id'], 'dayint' => $dayInt]);
                if ($data['amount'] > 0) {
                    $dayCount['rec_money'] += $data['amount'];  //统计充值金额
                    $dayCount->save();
                }
                //发起推送给接单会员
                PushFacade::pushToMember('微导游订单', $data['service_date'] . '您有新的预约单，请及时查看并回复哦！', $toMemberId);
            }
            if ($dealStatus == 2) {
                //已接单
                PushFacade::pushToMember('微导游接单', '您心仪的微导游已经确认订单啦，请享受您的旅程！', $data['member_id']);
            }
            if ($dealStatus == 3) {
                if ($cancel_type == 1) {
                    //会员取消,推送
                    PushFacade::pushToMember('取消微导游订单', '喔喔！' . $data['service_date'] . '您的预约单已被取消，请及时查看并联系客服退款！', $toMemberId);
                }
                if ($cancel_type == 2) {
                    //主播取消
                    PushFacade::pushToMember('取消微导游订单', '很抱歉的通知您，微导游因个人原因' . $data['service_date'] . '无法完成赴约，请及时查看并联系客服退款！', $toMemberId);
                }
                if ($cancel_type == 3) {
                    //平台取消
                    PushFacade::pushToMember('取消微导游订单', '抱歉！' . $data['service_date'] . '您的预约单由于主播长时未接单已被系统自动拒绝订单，请联系客服退款！', $data['member_id']);
                }
            }
            DB::commit();
            return $this->succeed();
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }


    /*
     * 定时核查通话中订单
     * */
    public function checkTalkOrder($orderid)
    {
        DB::beginTransaction();
        try {
            //查询订单
            $talkmodel = MemberTalk::find($orderid);
            if (isset($talkmodel)) {
                $config = Cache::get('SystemBasic');
                $yebz = $config['yebz_remind'] == 0 ? 120 : $config['yebz_remind']; //提前2分钟提示
                $syseconds = 0;
                $totalS = $talkmodel['times']; //已通话总秒数
                $status = $talkmodel['status']; //通话状态
                if ($status == 1) { //正在聊天
                    $memberAccount = MemberAccount::where('member_id', $talkmodel->member_id)->first();
//                    $this->logs('$memberAccount', $memberAccount);
                    $sygold = $memberAccount->balance_gold; //用户当前剩余金币
                    $price = $talkmodel->price; //单价

                    //计算冻结
                    $nowTime = date("Y-m-d H:i:s", time());
                    $totalS = strtotime($nowTime) - strtotime($talkmodel->begin_time); //总秒数

                    if ($price > 0) {
                        $ky_seconds = $sygold / $price * 60; //剩余可通话时长(秒数)
                        // $ky_seconds = ($sygold - (ceil($totalS / 60) * $price)) / $price * 60; //剩余可通话时长(秒数)


                        if ($sygold < $price || $sygold < 0 || $ky_seconds < 30) {
                            //剩余金币不足，或可通话秒数不足 直接挂断，避免产生负数
                            return $this->dealTalkOrder($talkmodel, 3);
                        }
                        $totalM = ceil($totalS / 60); //聊天总分钟数
//                            $ydjM = $talkmodel->amount / $talkmodel->price; //已冻结分钟
                        $bcdjAmount = $talkmodel->price * $totalM; //修改冻结金额
                        if ($bcdjAmount > 0) {
                            //需要累加冻结金币
                            $talkmodel->amount = $bcdjAmount; //总消费
                            $memberAccount->notuse_gold = $bcdjAmount; //直接修改
                            $memberAccount->save();
                        }
                        $talkmodel->times = $totalS; //总秒数
                        $talkmodel->save();
                        $syseconds = $ky_seconds;
                    } else {
                        //不收费
                        $syseconds = 99999;
                        $talkmodel->times += $totalS; //总秒数
                        $talkmodel->save();
                    }
                } else if ($status == 0) {
                    $nowTime = date("Y-m-d H:i:s", time());
                    $totalS = strtotime($nowTime) - strtotime($talkmodel->created_at); //已发起秒数
                    if ($totalS > 60) {
                        //超过1分钟未接听,挂断
                        $this->dealTalkOrder($talkmodel, 2);
                    }
                    $totalS = 0; //未通话
                }

                $retData = array(
                    'status' => $talkmodel['status'],
                    'status_cn' => $talkmodel['status_cn'],
                    'remind_seconds' => $yebz,
                    'canuse_seconds' => $syseconds,
                    'call_seconds' => $totalS,
                    'zbsy_gold' => 0
                );
                DB::commit();
//                $this->logs('checkTalkOrder', $retData);
                return $this->succeed($retData);
            }
            return $this->failure(1, '未找到通话订单!');
        } catch (\Exception $ex) {
            $this->logs('检查通话订单异常', $ex);
            return $this->failure(1, '检查订单异常', $ex->getMessage());
        }
    }

    /*
     * 会员添加到指定标签
     * */
    public function memberToTag($memberId, $groupStr)
    {
        try {

            $groupAry = explode(',', $groupStr);
            foreach ($groupAry as $key => $item) {
                $model = MemberTags::firstOrNew(['member_id' => $memberId, 'tag_id' => $item]);
                $this->logs('保存分组', $model);
                $model->save();
            }

            DB::commit();
            return $this->succeed();
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }
}
