<?php
/**
 *  会员信息控制器
 */

namespace App\Http\Controllers\System\Member;

use App\Facades\CommonFacade;
use App\Facades\RechargeFacade;
use App\Facades\RecordFacade;
use App\Http\Controllers\Controller;
use App\Http\Repositories\MemberLevelRepository;
use App\Models\MemberAccount;
use App\Models\MemberGroup;
use App\Models\MemberInfo;
use App\Models\MemberLevel;
use App\Models\MemberRecharge;
use App\Models\MemberRecord;
use App\Repositories\MemberInfoRepository;
use App\Utils\Helper;
use App\Utils\SelectList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MemberInfoController extends Controller
{
    public function __construct(MemberInfoRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        $statusList = SelectList::statusList();
        $onlineStatusList = SelectList::onLineStatusList();
        $busyStatusList = SelectList::busyStatusList();
        $selfieCheckList = SelectList::yesOrNo();
        $realNameCheckList = SelectList::yesOrNo();
        $businessCheckList = SelectList::yesOrNo();
        return view('member.info.index', compact('statusList', 'onlineStatusList', 'busyStatusList', 'selfieCheckList', 'realNameCheckList', 'businessCheckList'));
    }

    /*
    *  显示列表(获取数据)
    * */
    public function lists()
    {
        try {
            $list = $this->repository->withCount(['childrens','inviterchilds'])->lists(null, ['account', 'parent', 'lastlogin','inviter','inviterzb']);
            return $this->paginate($list);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }


    /**
     * 禁用和启用
     * @param Request $request
     * @return array|mixed
     */
    public function status(Request $request)
    {
        try {
            $list = $this->repository->find($request->id);
            $status = $list['status'] == 1 ? 0 : 1;
            $result = $this->repository->update(['id' => $request->id, 'status' => $status]);
            return $result;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * 是否开通邀请人
     * @param Request $request
     * @return array|mixed
     */
    public function inviter(Request $request)
    {
        try {
            $id = $request->id;
            $info = MemberInfo::where('id',$id)->first();
            $info->is_inviter = $info->is_inviter == 1 ? 0 : 1;
            if ($info->save()) {
                return $this->succeed('操作成功','操作成功');
            } else {
                return $this->validation('操作失败');
            }
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * 是否开通经纪人
     * @param Request $request
     * @return array|mixed
     */
    public function inviterzb(Request $request)
    {
        try {
            $id = $request->id;
            $info = MemberInfo::where('id',$id)->first();
            $info->is_inviter_zb = $info->is_inviter_zb == 1 ? 0 : 1;
            if ($info->save()) {
                return $this->succeed('操作成功','操作成功');
            } else {
                return $this->validation('操作失败');
            }
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }
    /**
     * 头像审核
     * @param Request $request
     * @return array|mixed
     */
    public function headAudit(Request $request)
    {
        try {
            $data = $this->repository->find($request->id);
            if ($request->filled('status')) {
                $data->head_pic = $data->new_head_pic;
            }
            $data->new_head_pic = Null;

            $result = $this->repository->update($data);
            return $result;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /*
     * 添加视图
     * */
    public function create()
    {
        $code = CommonFacade::randStr(6, 'NUMBER');
        $level = MemberLevel::where('status', 1)->get(['id', 'name']); //会员等级
        $group = MemberGroup::where('status', 1)->get(['id', 'name']); //会员分组
        return view('member.info.create', compact('code', 'level', 'group'));
    }

    /*
    * 保存到数据库
    * */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            if (isset($data['pid'])) {
                //先验证推荐人编号是否存在
                $tjr = $this->repository->findBy('code', $data['pid']);
                if (!isset($tjr)) {
                    return $this->failure(1, '推荐人编号错误！');
                }
                if ($tjr->status == 0) {
                    return $this->failure(1, '推荐人账户已被禁用！');
                }
                $data['pid'] = $tjr->id;
            }

            $res = MemberInfo::where(['code' => $data['code']])->first();
            if ($res != null) {
                return $this->failure(1, '会员编号已经存在！');
            }
            $res = MemberInfo::where(['user_name' => $data['user_name']])->first();
            if ($res != null) {
                return $this->failure(1, '会员用户名已经存在！');
            }

            //生成邀请码
            $data['invitation_code'] = CommonFacade::randStr(7, 'NUMBER');
            $data['reg_time'] = Helper::getNowTime(); //注册时间
            $data['reg_ip'] = CommonFacade::getIP(); //注册IP
            $result = $this->repository->store($data);
            if ($result['status']) {

                //注册成功后，判断是否有注册赠送
                $config = Cache::get('SystemBasic'); //取平台配置
                $zxyqrGold = $config->yqzc_give_gold; //注册邀请人奖励金币
                if ($zxyqrGold > 0 && isset($data['pid'])) {
                    //注册赠送邀请人
                    $beforeAmount = $tjr->account->surplus_gold;
                    $afterAmount = $beforeAmount + $zxyqrGold;

                    $record = new MemberRecord();
                    $record->member_id = $tjr->id;
                    $record->type = 24;//注册赠送邀请人
                    $record->account_type = 1; //账户类型
                    $record->amount = $zxyqrGold; //发生金额
                    $record->freeze_amount = 0;//冻结金额
                    $record->before_amount = $beforeAmount;//变动前额
                    $record->balance = $afterAmount;//实时余额
                    $record->status = 1;//交易成功
                    $record->remark = '邀请注册奖励金币';//交易备注

                    return RecordFacade::addRecord($record); //调用新增资金流水

                }

                return $this->succeed($result);
            }
            return $this->failure(1, $result['msg']);
        } catch (\Exception $ex) {

            return $this->exception($ex);
        }
    }

    /*
     * 修改界面
     * */
    public function edit(Request $request)
    {
        try {
            $model = $this->repository->find($request->id);
            $level = MemberLevel::where('status', 1)->get(['id', 'name']); //会员等级
            $group = MemberGroup::where('status', 1)->get(['id', 'name']); //会员分组
            return view('member.info.edit', compact('level', 'group'))->with('memberInfo', $model);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /*
    * 修改数据到数据库
    * */
    public function update(Request $request)
    {
        try {
            $data = $request->all();
            $result = $this->repository->update($data);
            return $result;
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
            $result = $this->repository->forceDelete($request->ids);
            return $result;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /*
     * 会员金币变动
     * */
    public function changeGold($mid)
    {
        $member = MemberInfo::where('id', $mid)->get(['id', 'user_name','nick_name'])->first();
        return view('member.info.changegold', compact('member'));
    }

    /*
    * 金币变动保存
    * */
    public function changeGoldSave(Request $request)
    {
        try {

            $memberId = $request['member_id']; //会员ID
            $type = $request['type']; //变动类型
            $accountType = 0; //$request['account']; //变动账户
            $quantity = $request['quantity']; //变动数量
            $status = $request['pay_status']; //支付状态
            $is_lock = $request['is_lock'];//判断是否可提现

            //判断会员是否存在
            $memberInfo = $this->repository->find($memberId);
            if ($memberInfo == null) {
                return $this->failure(1, '会员不存在！');
            }
            //1.添加会员充值记录
            $recItem = new MemberRecharge();
            $recItem->member_id = $memberId;
            $recItem->order_no = Helper::getNo(); //编号
            $recItem->type = $type; //变动类型
            $recItem->way = 5; //手动
            $recItem->amount = 0;
            $recItem->quantity = $quantity; //数量
            $recItem->status = $status; //成功
            $recItem->remark = $request['remark']; //备注
            $recItem->is_sys = $request['is_sys']; //是后台操作
            $recItem->operator = Auth::user()->name; //操作员
            return RechargeFacade::rechargeDeal($recItem, $accountType,$is_lock);


        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /*
     * 设置会员邀请人
     * */
    public function setPid($mid)
    {
        $member = MemberInfo::find($mid);
        return view('member.info.setpid', compact('member'));
    }

    /*
       * 设置会员邀请人保存
       * */
    public function setPidSave(Request $request)
    {
        try {

            $memberId = $request['member_id']; //会员ID
            $pcode = $request['pcode']; //邀请人编号
            $agentCode = $request['agent_code'];//经纪人编号
            if (!isset($pcode) && !isset($agentCode)) {
                return $this->failure(1, '请输入设置邀请人或经纪人！');
            }
            $parentId = 0;
            if (isset($pcode)) {
                //通过上级会员编号查询会员
                $parentMember = $this->repository->findWhere(['code' => $pcode])->first();
                if (!isset($parentMember)) {
                    return $this->failure(1, '邀请人编号错误！');
                }
                if($parentMember->is_inviter === 0){
                    return $this->failure(1, '未开通邀请人权限！');
                }
                if ($parentMember->status == 0) {
                    return $this->failure(1, '邀请人已禁用！');
                }
                if ($parentMember->pid == $memberId) {
                    return $this->failure(1, '会员之间不能相互邀请！');
                }
                if ($parentMember->id == $memberId){
                    return $this->failure(1, '不能设置自己为邀请人！');
                }
                $parentId = $parentMember->id;
                $pId = $parentMember->id;
            }
            $agentId = 0;
            if (isset($agentCode)) {
                //设置经纪人
                $agentMember = $this->repository->findWhere(['code' => $agentCode])->first();
                if (!isset($agentMember)) {
                    return $this->failure(1, '经纪人编号错误！');
                }
                if($agentMember->is_inviter_zb === 0){
                    return $this->failure(1, '未开通经纪人权限！');
                }
                if ($agentMember->status == 0) {
                    return $this->failure(1, '经纪人已禁用！');
                }
                if ($agentMember->id == $memberId){
                    return $this->failure(1, '不能设置自己为经纪人！');
                }
                $agentId = $agentMember->id;
                $pId = $agentMember->id;
            }
            $member = $this->repository->find($memberId);
            $member->pid = $pId;
            if ($parentId != 0){
                $member->inviter_id = $parentId;
            }
            if ($agentId != 0){
                $member->inviter_zbid = $agentId;
            }
            $result = $this->repository->update($member);
            return $result;

        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }


}
