<?php

namespace App\Http\Controllers\System\Member;

use App\Facades\RecordFacade;
use App\Http\Controllers\Controller;
use App\Http\Repositories\MemberExchangeRepository;
use App\Models\MemberInfo;
use App\Models\MemberRecord;
use App\Models\SystemRec;
use App\Repositories\MemberInfoRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

//会员金币兑换记录
class MemberExchangeController extends Controller
{
    public function __construct(MemberExchangeRepository $repository)
    {
        $this->repository = $repository;
    }

    //页面首页
    public function index()
    {
        return view('member.exchange.index');
    }

    /*
     * 显示列表
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
    public function create(MemberInfoRepository $memberInfoRepository)
    {
        $config = Cache::get('SystemBasic'); //取平台
        $dataList = SystemRec::where(['status' => 1, 'type' => 1])->orderBy('sort', 'desc')->get(['cost', 'quantity']);
        $member = $memberInfoRepository->findWhere(['status' => 1], ['id', 'sex', 'code', 'user_name', 'nick_name']); //会员
        return view('member.exchange.create', compact('dataList', 'member', 'config'));
    }

    /*
     * 添加数据库
     * */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $gold = $data['gold']; //兑换金币
            $memberId = $data['member_id'];
            //1.先判断会员
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
            if ($memberInfo->sex == 0) {
                //会员，非主播
                $candhGold = $memberInfo->account->cantx_gold; //可兑换金币
            }
            if ($candhGold < $gold) {
                return $this->failure(1, '会员剩余可兑换金币不足！',[$candhGold,$gold]);
            }
            $result = $this->repository->store($data);
            if ($result['status']) {
                //数据保存成功后，同时进行资金处理
                //1.会员先扣除可用余额
                $beforeAmount = $memberInfo->account->surplus_gold;
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

                //2.余额账号增加
                $rmb = $data['rmb'];
                $beforeAmount = $memberInfo->account->surplus_rmb;
                $afterAmount = $beforeAmount + $rmb;

                $zbRecord = new MemberRecord();
                $zbRecord->member_id = $memberId; //主播ID
                $zbRecord->type = 3;//兑换
                $zbRecord->account_type = 0; //余额账户类型
                $zbRecord->amount = $rmb; //发生金额
                $zbRecord->freeze_amount = 0;//冻结金额
                $zbRecord->before_amount = $beforeAmount;//变动前额
                $zbRecord->balance = $afterAmount;//实时余额
                $zbRecord->status = 1;//交易成功
                $zbRecord->remark = '兑换资金';//交易备注

                return RecordFacade::addRecord($record, $zbRecord); //调用新增资金流水
            }
            return $this->failure(1, $result['msg']);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /*
     * 删除
     * */
    public function destroy(Request $request)
    {
        try {
            $result = $this->repository->destroy($request->ids);
            return $result;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }
}
