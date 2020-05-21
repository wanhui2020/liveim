<?php
/**
 *  会员打赏管理控制器
 */

namespace App\Http\Controllers\System\Member;

use App\Facades\RecordFacade;
use App\Http\Controllers\Controller;
use App\Models\MemberAccount;
use App\Models\MemberExtend;
use App\Models\MemberInfo;
use App\Models\MemberRecord;
use App\Models\MemberReward;
use App\Models\SystemGift;
use App\Repositories\MemberRewardRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MemberRewardController extends Controller
{
    public function __construct(MemberRewardRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        return view('member.reward.index');
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

    /*
     * 添加视图
     * */
    public function create()
    {
        $member = MemberInfo::where(['status' => 1, 'sex' => 0])->get(['id', 'code', 'user_name', 'nick_name']); //打赏会员
        $tomember = MemberInfo::where(['status' => 1, 'sex' => 1])->get(['id', 'code', 'user_name', 'nick_name']); //接收会员（主播）
        return view('member.reward.create', compact('member', 'tomember'));
    }

    /*
    * 保存到数据库
    * */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $memberId = $data['member_id']; //赠送会员
            $toMemberId = $data['to_member_id']; //接收会员
            $memberExtend = MemberExtend::where('member_id', $toMemberId)->first(); //主播扩展
            $gold = $data['gold']; //需要的金币
            $result = $this->repository->store($data);

            //2.判断查看会员余额是否足够
            if ($result['status']) {
                if ($gold > 0) {

                    $memberAccount = MemberAccount::where('member_id', $memberId)->first(); //会员
                    if ($memberAccount->balance_gold < $gold) {
                        return $this->failure(1, '该会员打赏金额超出可用余额！');
                    }
                    //1.会员扣除资费默认先扣可用余额
                    $beforeAmount = $memberAccount->surplus_gold;
                    $accountType = 1;
                    if ($memberAccount->surplus_gold < $gold) {
                        //不可提现账户
                        $accountType = 2;
                        $beforeAmount = $memberAccount->notuse_gold;
                    }
                    $bqAmount = -$gold; //减少
                    $record = new MemberRecord();
                    $record->member_id = $memberId;
                    $record->type = 32;//打赏
                    $record->account_type = $accountType; //账户类型
                    $record->amount = $bqAmount; //发生金额
                    $record->freeze_amount = 0;//冻结金额
                    $record->before_amount = $beforeAmount;//变动前额
                    $record->balance = $beforeAmount + $bqAmount;//实时余额
                    $record->status = 1;//交易成功
                    $record->remark = '打赏';//交易备注

                    //2.主播收益
                    //获取平台基础数据设置，抽成比例
                    $rate = $memberExtend->other_rate; //默认取主播自身其他消费配置的，如果未单独设置，则取平台默认值
                    if ($rate == 0) {
                        $config = Cache::get('SystemBasic'); //取平台
                        $rate = $config->ds_rate;
                    }
                    $sxf = round($gold * ($rate / 100)); //手续费，四舍五入
                    $syGold = $gold - $sxf; //主播收益

                    $zhuboAccount = MemberAccount::where('member_id', $toMemberId)->first(); //主播账户
                    $remark = '收益(' . $syGold . ')=' . $gold . ' - 手续费(' . $sxf . ')';
                    $beforeAmount = $zhuboAccount->surplus_gold;

                    $zbRecord = new MemberRecord();
                    $zbRecord->member_id = $toMemberId; //主播ID
                    $zbRecord->type = 33;//收到打赏
                    $zbRecord->account_type = 1; //账户类型
                    $zbRecord->amount = $syGold; //发生金额
                    $zbRecord->freeze_amount = 0;//冻结金额
                    $zbRecord->before_amount = $beforeAmount;//变动前额
                    $zbRecord->balance = $beforeAmount + $syGold;//实时余额
                    $zbRecord->status = 1;//交易成功
                    $zbRecord->remark = $remark;//交易备注
                    return RecordFacade::addRecord($record, $zbRecord); //调用新增资金流水
                }
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

}