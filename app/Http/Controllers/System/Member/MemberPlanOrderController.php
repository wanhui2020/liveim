<?php
/**
 *  主播会员商务订单控制器
 */

namespace App\Http\Controllers\System\Member;

use App\Facades\MemberFacade;
use App\Http\Controllers\Controller;
use App\Models\MemberExtend;
use App\Models\MemberInfo;
use App\Models\MemberPlan;
use App\Repositories\MemberPlanOrderRepository;
use App\Utils\Helper;
use App\Utils\SelectList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MemberPlanOrderController extends Controller
{
    public function __construct(MemberPlanOrderRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        $status = SelectList::planOrderStatus();
        $pay_status = SelectList::planOrderPayStatus();
        return view('member.plan.order.index', compact('status', 'pay_status'));
    }

    /*
    *  显示列表(获取数据)
    * */
    public function lists()
    {
        try {
            $list = $this->repository->with(['data'])->lists(null, ['member', 'tomember']);
            return $this->paginate($list);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }


    /*
     * 添加视图
     * */
    public function create()
    {
        $member = MemberInfo::where(['status' => 1, 'sex' => 0])->get(['id', 'code', 'user_name', 'nick_name']); //发起会员
        $tomember = MemberInfo::where(['status' => 1, 'sex' => 1, 'business_check' => 1])->get(['id', 'code', 'user_name', 'nick_name']); //接收会员（主播,已认证且开起商务）
        $config = Cache::get('SystemBasic'); //取平台配置
        $fee = array(
            'sin' => $config['business_sin_fee'],
            'mul' => $config['business_mul_fee']
        );
        return view('member.plan.order.create', compact('member', 'tomember', 'fee'));
    }

    /*
    * 保存到数据库
    * */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $proid = rtrim($data['project'], ',');
            //先判断主播是否已开启商务接单
            $memberAccount = MemberExtend::where('member_id', $data['to_member_id'])->first();
            if ($memberAccount->is_business == 0) {
                return $this->failure(1, '该主播暂未开启商务服务！');
            }
            $data['order_no'] = Helper::getNo();
            return MemberFacade::addPlanOrder($data, $proid);
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

            $data = $this->repository->find($request->id); //订单对象
            $dealStatus = $request->status; //处理方式
            if (!isset($data)) {
                return $this->failure(1, '商务订单不存在！');
            }
            $memberId = $data['member_id']; //发起会员
            $toMemberId = $data['to_member_id']; //接收主播
            $amount = $data['amount']; //消费金额
            $cancel_type = 0;
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
                $cancel_type = 3;
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
            } else {
                //结算操作
                $data['status'] = 6;
            }
            return MemberFacade::dealPlanOrder($data, $dealStatus, $cancel_type); //调用处理方法
//            $result = $this->repository->update($data);
//            if ($result['status']) {
//                if ($dealStatus == 6) {
//                    //结算
//                    $profit = $data['profit']; //主播收益
//                    $zhuboAccount = MemberAccount::where('member_id', $toMemberId)->first(); //主播账户
//                    //1.会员扣除资费默认先扣可用余额
//                    $beforeAmount = $zhuboAccount->surplus_rmb; //可用余额
//                    $afterAmount = $beforeAmount + $profit;
//
//                    $record = new MemberRecord();
//                    $record->member_id = $toMemberId;
//                    $record->type = 34;//商务邀约收益
//                    $record->account_type = 0; //余额
//                    $record->amount = $profit; //发生金额
//                    $record->freeze_amount = 0;//冻结金额
//                    $record->before_amount = $beforeAmount;//变动前额
//                    $record->balance = $afterAmount;//实时余额
//                    $record->status = 1;//交易成功
//                    $record->remark = '商务邀约收益';//交易备注
//
//                    return RecordFacade::addRecord($record);
//
//                }
//                return $this->succeed($result, '商务订单处理成功！');
//
//            }
//            return $this->failure(1, '商务订单处理失败！');


//            if ($dealStatus == 1) {
//                $data['status'] = $dealStatus; //修改处理状态
//                $result = $this->repository->update($data);
//                if ($result['status'] && $gold > 0) {
//                    $config = Cache::get('SystemBasic'); //取平台配置
//                    $rate = $config->hy_rate; //换衣收成占比
//                    //接受换衣，扣费
//                    $memberAccount = MemberAccount::where('member_id', $memberId)->first(); //会员
//                    $zhuboAccount = MemberAccount::where('member_id', $toMemberId)->first(); //主播账户
//                    //1.会员扣除资费默认先扣可用余额
//                    $beforeAmount = $memberAccount->surplus_gold;
//                    $afterAmount = $beforeAmount - $gold;
//
//                    $record = new MemberRecord();
//                    $record->member_id = $memberId;
//                    $record->type = 28;//付费换衣服
//                    $record->account_type = 1; //账户类型
//                    $record->amount = -$gold; //发生金额
//                    $record->freeze_amount = 0;//冻结金额
//                    $record->before_amount = $beforeAmount;//变动前额
//                    $record->balance = $afterAmount;//实时余额
//                    $record->status = 1;//交易成功
//                    $record->remark = '付费换衣服';//交易备注
//
//                    //2.主播收益
//                    $sxf = round($gold * ($rate / 100)); //手续费，四舍五入
//                    $syGold = $gold - $sxf; //主播收益
//                    $beforeAmount = $zhuboAccount->surplus_gold;
//                    $remark = '收益(' . $syGold . ')=' . $gold . ' - 手续费(' . $sxf . ')';
//
//                    $zbRecord = new MemberRecord();
//                    $zbRecord->member_id = $toMemberId; //主播ID
//                    $zbRecord->type = 29;//换衣收益
//                    $zbRecord->account_type = 1; //账户类型
//                    $zbRecord->amount = $syGold; //发生金额
//                    $zbRecord->freeze_amount = 0;//冻结金额
//                    $zbRecord->before_amount = $beforeAmount;//变动前额
//                    $zbRecord->balance = $beforeAmount + $syGold;//实时余额
//                    $zbRecord->status = 1;//交易成功
//                    $zbRecord->remark = $remark;//交易备注
//
//
//                    return RecordFacade::addRecord($record, $zbRecord);
//                }
//                return $this->failure(1, $result['msg']);
//
//
//            } else {
//                if ($dealStatus == 2 && $data['status'] != 1) {
//                    //已完成订单，必须是换中的订单
//                    return $this->failure(1, '换衣订单不能进行完成操作！');
//                }
//                if ($dealStatus == 3 && $data['status'] != 0) {
//                    //取消订单
//                    return $this->failure(1, '换衣订单已不能取消！');
//                }
//                //结束或拒接订单
//                $data['status'] = $dealStatus; //修改处理状态
//                $result = $this->repository->update($data);
//                if ($result['status']) {
////                    if ($dealStatus == 2) {
////                        //换衣完成，需要执行通知会员发起视频聊天
////                    }
//                    return $this->succeed($result, '换衣订单处理成功！');
//
//                }
//                return $this->failure(1, '换衣订单处理失败！');
//
//            }

        } catch (\Exception $ex) {

            return $this->exception($ex);
        }
    }


    public function getProjectList(Request $request)
    {
        $list = MemberPlan::where('member_id', $request->mid)->orderBy('sort', 'desc')->get(['id', 'project', 'content']);
        return $list;
    }
}
