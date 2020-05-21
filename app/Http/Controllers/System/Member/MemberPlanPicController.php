<?php
/**
 *  会员商务计划图片管理控制器
 */

namespace App\Http\Controllers\System\Member;

use App\Http\Controllers\Controller;
use App\Models\MemberPlan;
use App\Repositories\MemberPlanPicRepository;
use App\Utils\SelectList;
use Illuminate\Http\Request;

class MemberPlanPicController extends Controller
{
    public function __construct(MemberPlanPicRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index(Request $request)
    {
        $plan = MemberPlan::find($request['id']);
        return view('member.plan.pic.index', compact('plan'));
    }

    /*
    *  显示列表(获取数据)
    * */
    public function lists(Request $request)
    {
        try {
            $addwhere = array();
            $addwhere['plan_id'] = $request['planid'];
            $list = $this->repository->lists($addwhere, ['plan']);
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
        return view('member.plan.pic.create');
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
            return view('member.plan.pic.edit')->with('cons', $model);
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
            $result = $this->repository->destroy($request->ids);
            return $result;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }


}