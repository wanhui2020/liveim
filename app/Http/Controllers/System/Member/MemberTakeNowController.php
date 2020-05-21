<?php
/**
 *  会员提现管理控制器
 */

namespace App\Http\Controllers\System\Member;

use App\Facades\RecordFacade;
use App\Http\Controllers\Controller;
use App\Models\MemberAccount;
use App\Models\MemberInfo;
use App\Models\MemberRecord;
use App\Models\MemberTakeNow;
use App\Models\SystemData;
use App\Repositories\MemberTakeNowRepository;
use App\Utils\Helper;
use App\Utils\SelectList;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MemberTakeNowController extends Controller
{
    public function __construct(MemberTakeNowRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        $status = SelectList::checkStatus();
        $way = SelectList::takeNowWay();
        return view('member.takenow.index', compact('status', 'way'));
    }

    /*
    *  显示列表(获取数据)
    * */
    public function lists()
    {
        try {
            $list = $this->repository->lists(null, ['member']);
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
        $member = MemberInfo::where(['status' => 1, 'realname_check' => 1])->with(['account'])->get(['id', 'code', 'user_name', 'nick_name', 'sex']); //已实名的会员
        $way = SelectList::takeNowWay();
        return view('member.takenow.create', compact('member', 'way'));
    }

    /*
    * 保存到数据库
    * */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $amount = $data['amount']; //提现金额
            $memberId = $data['member_id']; //会员
            //1.先判断会员是否可用
            $memberInfo = MemberInfo::find($memberId);
            if ($memberInfo == null || $memberInfo->status == 0) {
                return $this->failure(1, '会员不存在!');
            }
            //2.判断会员是否已进行了实名认证
            if ($memberInfo->realname_check == 0) {
                return $this->failure(1, '该会员未进行实名实证，不能提现!');
            }
            $config = Cache::get('SystemBasic'); //取平台参数
            if ($amount < $config->tx_min) {
                return $this->failure(1, '会员单笔最低提现' . $config->tx_min . '元！');
            }
            $cantxRmb = $memberInfo->account->cantx_rmb; //剩余可提现余额
            if ($cantxRmb < $amount) {
                return $this->failure(1, '会员剩余可提现余额不足！');
            }
            //3.判断提现是否收取手续费
            //获取今日提现次数
            $start_time = Carbon::now()->startOfDay();
            $end_time = Carbon::now()->endOfDay();
            $ytxlist = $this->repository->findWhere(['status' => 1, 'member_id' => $memberId])->whereBetween('deal_time', [$start_time, $end_time]);
            $ytxCount = $ytxlist->count();
            if ($ytxCount >= $config->tx_max_count) {
                return $this->failure(1, '该会员当日提现次数已用完！');
            }
            if ($ytxCount > 0) {
                //已提现过，判断提现额度是否超限
                $ytxAmount = $ytxlist->sum('amount');
                $syktx = $config->tx_max_count - $ytxAmount;
                if ($syktx < 0) {
                    return $this->failure(1, '该会员当日最多还可提现' . $syktx . '元！');
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
            $result = $this->repository->store($data);
            if ($result['status']) {
                return $this->succeed($result);
            }
            return $this->failure(1, $result['msg']);
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
     * 处理审核视图
     * */
    public function deal(Request $request)
    {
        $model = $this->repository->find($request->id);
        return view('member.takenow.deal')->with('cons', $model);
    }
    /**
     * 处理保存
     * @param Request $request
     * @return array
     */
    public function dealSave(Request $request)
    {
        try {
            /**
             * 处理审核  根据是否通过进行处理
             * 判断会员是否存在 会员状态 会员钱包
             * 通过
             * 锁表 处理提现订单 更新订单状态
             * 加入审核人和时间
             * 减去冻结的金额  减去可用余额
             * 拒绝
             * 锁表 处理提现订单 更新订单状态
             * 加入审核人和时间
             * 只减去冻结金额 余额不变
             */
            $data = $request->all();
            $memberInfo = MemberInfo::where('id',$data['member_id'])->first(); //会员信息
            if (!isset($memberInfo)){
                return $this->validation('会员不存在，请核实后处理！');
            }
            if ($memberInfo->status === 0){
                return $this->validation('该会员已被禁用，不能进行提现！');
            }
            $memberAcc = $memberInfo->account; //会员账户
            if ($memberAcc->notuse_rmb < 0){
                return $this->validation('用户资金异常，请核实后处理！');
            }
            $memberId = $data['member_id']; //会员ID
            $data['deal_user'] = Auth::user()->name;
            $data['deal_time'] = Helper::getNowTime();
            if ($data['status'] == 1){ //审核通过
                /**
                 * 锁表加事务处理
                 * try 保护
                 */
                DB::beginTransaction();
                try{
                    $take = MemberTakeNow::where('id',$data['id'])->lockForUpdate()->first();
                    /**
                     *
                     * 加入审核人和时间
                     * 减去冻结的金额  减去可用余额
                     * 写流水
                     */

                    $amount = floatval($data['amount']);
                    $dealAmount = floatval($data['real_amount']);
                    $feeMoney = floatval($data['fee_money']);

                    $beforeAmount = $memberInfo->account->surplus_rmb;
                    $afterAmount = $beforeAmount - $amount;

                    $take->member_id = $memberId;
                    $take->deal_user = $data['deal_user'];
                    $take->deal_time = $data['deal_time'];
                    $take->order_no = $data['order_no'];
                    $take->amount = $amount;
                    $take->fee_money = $feeMoney;
                    $take->real_amount = $dealAmount;
                    $take->account_name = $data['account_no'];
                    $take->deal_reason = $data['deal_reason'];
                    $take->status = $data['status'];
                    $memberInfo->account->surplus_rmb -= $data['amount'];
                    $memberInfo->account->notuse_rmb -= $data['amount'];
                    if (!$memberInfo->account->save()){
                        DB::rollBack();
                        return $this->validation('处理订单异常，请联系管理员!');
                    }

                    $remark = $dealAmount . '(获得金额)=' . $amount . '(提现金额)-' . $feeMoney . '(手续费)'; //备注说明
                    $record = new MemberRecord();
                    $record->member_id = $memberId;
                    $record->type = -1;//提现
                    $record->account_type = 0; //账户类型
                    $record->amount = -$amount; //发生金额
                    $record->freeze_amount = 0;//冻结金额
                    $record->before_amount = $beforeAmount;//变动前额
                    $record->balance = $afterAmount;//实时余额
                    $record->status = 1;//交易成功
                    $record->remark = $remark;//交易备注
                    if (!$record->save()){
                        DB::rollBack();
                        return $this->validation('处理订单异常，请联系管理员!');
                    }
                    if (!$take->save()){
                        DB::rollBack();
                        return $this->validation('处理订单异常，请联系管理员!');
                    }
                    DB::commit();
                    return $this->succeed();
                }catch (\Exception $ex){
                    DB::rollBack();
                    return $this->exception($ex,'处理订单异常，请联系管理员!');
                }
            }
            else{ //审核不通过
                /**
                 * 锁表加事务处理
                 */
                DB::beginTransaction();
                try{
                    $take = MemberTakeNow::where('id',$data['id'])->lockForUpdate()->first();
                    /**
                     *
                     * 加入审核人和时间
                     * 只减去冻结金额 余额不变
                     */
                    if ($take->status != 0){
                        return $this->validation('审核状态错误！');
                    }
                    $amount = floatval($data['amount']);
                    $dealAmount = floatval($data['real_amount']);
                    $feeMoney = floatval($data['fee_money']);

                    $take->member_id = $memberId;
                    $take->deal_user = $data['deal_user'];
                    $take->deal_time = $data['deal_time'];
                    $take->order_no = $data['order_no'];
                    $take->amount = $amount;
                    $take->fee_money = $feeMoney;
                    $take->real_amount = $dealAmount;
                    $take->account_name = $data['account_no'];
                    $take->deal_reason = $data['deal_reason'];
                    $take->status = $data['status'];
                    $memberInfo->account->notuse_rmb -= $data['amount'];
                    if ($memberInfo->account->notuse_rmb < 0){
                        DB::rollBack();
                        return $this->validation('处理订单异常，请联系管理员!');
                    }
                    if (!$memberInfo->account->save()){
                        DB::rollBack();
                        return $this->validation('处理订单异常，请联系管理员!');
                    }
                    if (!$take->save()){
                        DB::rollBack();
                        return $this->validation('处理订单异常，请联系管理员!');
                    }
                    DB::commit();
                    return $this->succeed();
                }catch (\Exception $ex){
                    DB::rollBack();
                    return $this->exception($ex,'处理订单异常，请联系管理员!');
                }
            }
        } catch (\Exception $ex) {
            return $this->exception($ex,'处理订单异常，请联系管理员!');
        }
    }

    /*
    * 处理保存
    * */
    public function dealSave1(Request $request)
    {
        try {
            $data = $request->all();
            $memberId = $data['member_id']; //会员ID
            $data['deal_user'] = Auth::user()->name;
            $data['deal_time'] = Helper::getNowTime();
            $result = $this->repository->update($data);

            if ($result['status']) {
                dd($data['status']);
                //审核通过进行处理
                if ($data['status'] == 1) { //审核通过
                    $amount = floatval($data['amount']);
                    $dealAmount = floatval($data['real_amount']);
                    $feeMoney = floatval($data['fee_money']);
                    $memberInfo = MemberInfo::find($memberId);
                    //数据保存成功后，同时进行资金处理
                    //1.会员先扣除可用余额
                    $beforeAmount = $memberInfo->account->surplus_rmb;
                    $afterAmount = $beforeAmount - $amount;
                    if ($afterAmount < 0){
                        return $this->validation('用户资金异常，请检查核对后通过！');
                    }
                    $remark = $dealAmount . '(获得金额)=' . $amount . '(提现金额)-' . $feeMoney . '(手续费)'; //备注说明
                    $record = new MemberRecord();
                    $record->member_id = $memberId;
                    $record->order_no = $data['order_no'];
                    $record->type = -1;//提现
                    $record->account_type = 0; //账户类型
                    $record->amount = -$amount; //发生金额
                    $record->freeze_amount = 0;//冻结金额
                    $record->before_amount = $beforeAmount;//变动前额
                    $record->balance = $afterAmount;//实时余额
                    $record->status = 1;//交易成功
                    $record->remark = $remark;//交易备注

                    return RecordFacade::addRecord($record); //调用新增资金流水
                }
            }
            return $this->failure(1, $result['msg']);

        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

}