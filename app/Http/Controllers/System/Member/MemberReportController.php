<?php
/**
 *  会员举报管理控制器
 */

namespace App\Http\Controllers\System\Member;

use App\Http\Controllers\Controller;
use App\Models\MemberInfo;
use App\Models\SystemData;
use App\Repositories\MemberReportRepository;
use App\Utils\Helper;
use App\Utils\SelectList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberReportController extends Controller
{
    public function __construct(MemberReportRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        $status = SelectList::dealStatus();
        return view('member.report.index', compact('status'));
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
        $member = MemberInfo::where('status', 1)->get(['id', 'code', 'user_name', 'nick_name']); //会员
        $tomember = MemberInfo::where('status', 1)->get(['id', 'code', 'user_name', 'nick_name']); //举报会员
        $type = SystemData::where(['status' => 1, 'type' => 7])->get(['value']); //举报类型
        return view('member.report.create', compact('member', 'tomember', 'type'));
    }

    /*
    * 保存到数据库
    * */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $result = $this->repository->store($data);
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
        return view('member.report.deal')->with('cons', $model);
    }


    /*
    * 处理保存
    * */
    public function dealSave(Request $request)
    {
        try {
            $data = $request->all();
            $data['deal_user'] = Auth::user()->name;
            $data['deal_time'] = Helper::getNowTime();
            $result = $this->repository->update($data);
            return $this->succeed($result);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

}