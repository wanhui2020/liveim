<?php

namespace App\Http\Controllers\System\Member;

use App\Facades\CommonFacade;
use App\Facades\RecordFacade;
use App\Http\Controllers\Controller;
use App\Http\Repositories\MemberSignInRepository;
use App\Models\MemberAccount;
use App\Models\MemberInfo;
use App\Models\MemberRecord;
use App\Models\MemberSignIn;
use App\Repositories\MemberInfoRepository;
use App\Utils\Helper;
use App\Utils\SelectList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MemberSignInController extends Controller
{
    public function __construct(MemberSignInRepository $repository)
    {
        $this->repository = $repository;
    }

    //页面首页
    public function index()
    {
        return view('member.signin.index');
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
        $member = $memberInfoRepository->findWhere(['status' => 1], ['id', 'code', 'user_name', 'nick_name']); //会员
        return view('member.signin.create', compact('member'));
    }

    /*
     * 添加数据库
     * */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $qd_date = date('y-m-d 00:00:00');
            if (isset($data['bq_date'])) {
                //补期日期
                $qd_date = $data['bq_date'];
            }
            //会员一天只能签到一次
            $res = $this->repository->findWhere(['member_id' => $data['member_id'], 'qd_date' => $qd_date])->first();
            if ($res != null) {
                return $this->failure(1, '该会员' . $qd_date . '已经签过到！');
            }
            $data['qd_date'] = $qd_date;

            if (!isset($data['bq_date'])) {
                //最新一次签到
                $preModel = MemberSignIn::where(['member_id' => $data['member_id']])->orderBy('qd_date', 'desc')->first();
                if ($preModel != null) {
                    //比较两个日期是否相差一天
                    $result = floor((strtotime($qd_date) - strtotime($preModel->qd_date)) / 86400);
                    if ($result == 1) {
                        $data['lx_days'] = $preModel->lx_days + 1;
                    }
                }
            } else {
                //判断是否设置了补签基础扣费
                $config = Cache::get('SystemBasic');
                $memberAccount = MemberAccount::where('member_id', $data['member_id'])->first();
                $bqCount = $memberAccount->bq_count; //补签次数
                $bqAmount = $config->bk_kf + 10 * $bqCount; //补签扣费
                if ($memberAccount->balance_gold < $bqAmount) {
                    return $this->failure(1, '该会员可用金币不足，不能补签！');
                }
            }

            $result = $this->repository->store($data); //保存补签数据
            if ($result['status']) {
                //补签要扣费
                if (isset($data['bq_date'])) {
                    if ($config != null && $config->bk_kf > 0) {

                        //默认先扣可用余额
                        $beforeAmount = $memberAccount->surplus_gold;
                        $afterAmount = $beforeAmount - $bqAmount;

                        $record = new MemberRecord();
                        $record->member_id = $data['member_id'];
                        $record->type = 7;//补签
                        $record->account_type = 1; //账户类型（金币账户）
                        $record->amount = -$bqAmount; //发生金额
                        $record->freeze_amount = 0;//冻结金额
                        $record->before_amount = $beforeAmount;//变动前额
                        $record->balance = $afterAmount;//实时余额
                        $record->status = 1;//交易成功
                        $record->remark = '补签扣费';//交易备注

                        return RecordFacade::addRecord($record); //调用新增资金流水
                    }
                }

                return $this->succeed($result);
            }
            return $this->failure(1, $result['msg']);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }


    /*
     * 渲染修改界面
     * */
    public function edit(Request $request)
    {
        try {
            $cons = $this->repository->find($request->id);
            return view('member.signin.edit')->with('cons', $cons);
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
