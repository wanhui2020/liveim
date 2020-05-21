<?php
/**
 *  会员实名认证管理控制器
 */

namespace App\Http\Controllers\System\Member;

use App\Http\Controllers\Controller;
use App\Models\MemberInfo;
use App\Repositories\MemberInfoRepository;
use App\Repositories\MemberRealNameRepository;
use App\Utils\Helper;
use App\Utils\SelectList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberRealNameController extends Controller
{
    public function __construct(MemberRealNameRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        $status = SelectList::checkStatus();
        return view('member.realname.index', compact('status'));
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
        $member = MemberInfo::where('realname_check', 0)->get(['id', 'code', 'user_name', 'nick_name']); //会员
        return view('member.realname.create', compact('member'));
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
        return view('member.realname.deal')->with('cons', $model);
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
                    $result = $memberInfoRepository->update(['id' => $data['member_id'], 'realname_check' => 1, 'true_name' => $data['name']]);
                }
                return $this->succeed($result);
            }
            return $result;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

}