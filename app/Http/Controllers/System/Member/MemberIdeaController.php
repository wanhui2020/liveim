<?php
/**
 *  会员自意见反馈管理控制器
 */

namespace App\Http\Controllers\System\Member;

use App\Http\Controllers\Controller;
use App\Models\MemberInfo;
use App\Repositories\MemberIdeaRepository;
use App\Utils\Helper;
use App\Utils\SelectList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberIdeaController extends Controller
{
    public function __construct(MemberIdeaRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        $status = SelectList::replayStatus();
        return view('member.idea.index', compact('status'));
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
        $member = MemberInfo::where('status', 1)->get(['id', 'code', 'user_name', 'nick_name']); //会员
        return view('member.idea.create', compact('member'));
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
        return view('member.idea.deal')->with('cons', $model);
    }


    /*
    * 处理保存
    * */
    public function dealSave(Request $request)
    {
        try {
            $data = $request->all();
            $data['replay_user'] = Auth::user()->name;
            $data['replay_time'] = Helper::getNowTime();
            $data['status'] = 1; //已回复
            $result = $this->repository->update($data);
            return $this->succeed($result);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

}