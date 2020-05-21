<?php
/**
 *  会员资源库管理控制器
 */

namespace App\Http\Controllers\System\Member;

use App\Facades\RecordFacade;
use App\Http\Controllers\Controller;
use App\Models\MemberAccount;
use App\Models\MemberExtend;
use App\Models\MemberFileView;
use App\Models\MemberInfo;
use App\Models\MemberRecord;
use App\Repositories\MemberFileRepository;
use App\Repositories\MemberFileViewRepository;
use App\Repositories\MemberInfoRepository;
use App\Utils\Helper;
use App\Utils\SelectList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MemberFileController extends Controller
{
    public function __construct(MemberFileRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        $status = SelectList::checkStatus();
        $type = SelectList::fileLibrary();
        return view('member.file.index', compact('status', 'type'));
    }

    /*
    *  显示列表(获取数据)
    * */
    public function lists()
    {
        try {
            $list = $this->repository->orderBy('status','asc')->lists(null, ['member']);
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
        $type = SelectList::fileLibrary();
        $member = MemberInfo::where(['status' => 1, 'sex' => 1])->get(['id', 'code', 'user_name', 'nick_name']); //主播会员
        return view('member.file.create', compact('type', 'member'));
    }

    /*
    * 保存到数据库
    * */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $result = $this->repository->store($data);
            if ($result['status']) {
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
            $type = SelectList::fileLibrary();
            $member = MemberInfo::where(['status' => 1, 'sex' => 1])->get(['id', 'code', 'user_name', 'nick_name']); //主播会员
            return view('member.file.edit', compact('type', 'member'))->with('cons', $model);
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
            if ($data['status'] == 2) {
                $data['status'] = 0; //拒绝的，编辑后需重新审核
            }
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
        $type = $model->type;
        return view('member.file.deal', compact('type'))->with('cons', $model);
    }


    /*
    * 处理保存
    * */
    public function dealSave(Request $request, MemberInfoRepository $memberInfoRepository)
    {
        try {
            $data = $request->all();
            $status = $data['status']; //状态
            $data['deal_user'] = Auth::user()->name;
            $data['deal_time'] = Helper::getNowTime();
            $result = $this->repository->update($data);
            return $result;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /*
     * 查看主播资源
     * */
    public function view(Request $request)
    {
        $member = MemberInfo::where(['status' => 1, 'sex' => 0])->get(['id', 'code', 'user_name', 'nick_name']); //会员
        $model = $this->repository->find($request->id);
        return view('member.file.view', compact('member'))->with('cons', $model);
    }


    /*
    * 查看资源保存
    * */
    public function viewSave(Request $request)
    {
        try {
            //查看资源
            $id = $request->id; //资源ID
            $type = $request->type; //资源类型
            $memberId = $request->member_id; //资源所属主播ID
            $viewMemberId = $request->view_member_id;//查看会员ID
            //先判断该会员是否查看过该记录
            $viewCounts = MemberFileView::where(['member_id' => $viewMemberId, 'member_file_id' => $id])->count();
            $gold = 0;
            if ($viewCounts == 0) {
                //未查看过，要扣费。
                $memberExtend = MemberExtend::where('member_id', $memberId)->first(); //主播扩展
                $gold = $type == 0 ? $memberExtend->video_view_fee : $memberExtend->picture_view_fee; //取查看费用
                //2.判断查看会员余额是否足够
                if ($gold > 0) {
                    $viewMemberAccount = MemberAccount::where('member_id', $viewMemberId)->first(); //会员
                    if ($viewMemberAccount->balance_gold < $gold) {
                        return $this->failure(1, '该会员可用金币不足，不能查看主播资源库！');
                    }
                }
            }
            //添加资源库查看记录
            $viewModel = new MemberFileView();
            $viewModel->member_id = $viewMemberId;
            $viewModel->to_member_id = $memberId;
            $viewModel->member_file_id = $id;
            $viewModel->gold = $gold;
            $viewModel->type = $type; //类型

            if ($viewModel->save()) {
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

                    return RecordFacade::addRecord($record, $zbRecord); //调用新增资金流水
                }
                return $this->succeed();
            }
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

}