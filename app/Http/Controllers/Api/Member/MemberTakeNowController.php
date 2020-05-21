<?php

namespace App\Http\Controllers\Api\Member;

use App\Facades\MemberFacade;
use App\Http\Controllers\Api\ApiController;
use App\Models\MemberExchange;
use App\Models\MemberTakeNow;
use App\Repositories\MemberTakeNowRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/*
 * 会员提现管理
 * */

class MemberTakeNowController extends ApiController
{

    public function __construct(MemberTakeNowRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 金币兑换
     * @param Request $request
     * @return array
     */
    public function goldExchange(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $memberId = $member->id;
            $gold = $request['gold']; //兑换金币数量
            //判断参数
            if (!isset($gold)) {
                return $this->validation('请输入所有必填参数！');
            }
            $data = new MemberExchange();
            $data['member_id'] = $memberId;
            $data['gold'] = $gold; //兑换金币
            return MemberFacade::goldExchange($data);
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /**
     * 会员提现申请
     * @param Request $request
     * @return array
     */
    public function apply(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $memberId = $member->id;
            $amount = $request['amount']; //提现金额
            $way = $request['way']; //提现方式(1微信 2支付宝 3银行卡)
            $account_no = $request['account_no']; //提现账号
            $account_name = $request['account_name']; //账号名称
            $bank = $request['bank']; //所属银行
            $tkpwd = $request['pwd']; //提现密码

            //参数验证
            if (!isset($amount) || !isset($way) || !isset($account_no) || !isset($tkpwd)) {
                return $this->validation('请输入所有必填参数！');
            }
            if (!in_array($way, [1, 2, 3])) {
                return $this->validation('提现方式参数错误！');
            }
            if ($amount <= 0) {
                return $this->validation('提现金额参数错误！');
            }
            if ($way == 3 && (!isset($bank) || !isset($account_name))) {
                return $this->validation('提现到银行卡账户名称和银行卡为必传！');
            }
            if (!isset($member['take_pwd'])){
                return $this->failure(1, '未设置交易密码！');
            }
            if (!Hash::check($tkpwd, $member['take_pwd'])) {
                return $this->failure(1, '提现密码错误！');
            }
            $data = new MemberTakeNow();
            $data['member_id'] = $memberId;
            $data['amount'] = $amount;
            $data['way'] = $way;
            $data['account_no'] = $account_no;
            $data['account_name'] = $account_name;
            $data['bank'] = $bank;
            return MemberFacade::takeNow($data);
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }
}
