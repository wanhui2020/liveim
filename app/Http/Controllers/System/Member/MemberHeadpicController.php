<?php
/**
 * 会员头像审核
 */

namespace App\Http\Controllers\System\Member;

use App\Http\Controllers\Controller;
use App\Models\MemberExtend;
use App\Models\MemberInfo;
use App\Repositories\MemberExtendRepository;
use App\Repositories\MemberInfoRepository;
use App\Repositories\MemberBusinessRepository;
use App\Utils\Helper;
use App\Utils\SelectList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberHeadpicController extends Controller
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
        return view('member.headpic.index', compact('statusList', 'onlineStatusList', 'busyStatusList', 'selfieCheckList', 'realNameCheckList', 'businessCheckList'));
    }

    /*
    *  显示列表(获取数据)
    * */
    public function lists()
    {
        try {
            $list = $this->repository->withCount(['childrens','inviterchilds'])->lists(null, ['account', 'parent', 'lastlogin','inviter','inviterzb'],0,1);
            return $this->paginate($list);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }
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
        $member = MemberInfo::where(['status' => 1, 'sex' => 1, 'business_check' => 0])->get(['id', 'code', 'user_name', 'nick_name']); //会员
        return view('member.headpic.create', compact('member'));
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
        return view('member.headpic.deal')->with('cons', $model);
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
            if ($result['status']) {
                //成功后进行处理，对通过的进行处理
                if ($status == 1) {
                    $result = $memberInfoRepository->update(['id' => $data['member_id'], 'business_check' => 1]);
//                    //默认开启
//                    $memberExtendRepository->update(['member_id' => $data['member_id'], 'is_business' => 1]);

                    $memberExtend = MemberExtend::where('member_id', $data['member_id'])->first();
                    $memberExtend->is_business = 1;
                    $memberExtend->save();


                }
                return $this->succeed($result);
            }
            return $result;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

}