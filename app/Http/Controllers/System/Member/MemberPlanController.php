<?php
/**
 *  主播会员商务服务行程管理控制器
 */

namespace App\Http\Controllers\System\Member;

use App\Http\Controllers\Controller;
use App\Models\MemberInfo;
use App\Models\MemberPlanPic;
use App\Models\SystemData;
use App\Repositories\MemberPlanRepository;
use Illuminate\Http\Request;

class MemberPlanController extends Controller
{
    public function __construct(MemberPlanRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        $projectList = SystemData::where(['status' => 1, 'type' => 8])->get(['value']); //服务项
        return view('member.plan.index', compact('projectList'));
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

    /**
     * 商务审核
     * @param Request $request
     * @return array|mixed
     */
    public function audit(Request $request)
    {
        try {
            $data = $this->repository->find($request->id);
            if ($request->filled('status')) {
                $data->status = 1;
            } else {
                $data->status = 0;
            }

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
        //必须是进行商务认证的主播
        $member = MemberInfo::where(['status' => 1, 'sex' => 1, 'business_check' => 1])->get(['id', 'code', 'user_name', 'nick_name']); //主播会员
        $projectList = SystemData::where(['status' => 1, 'type' => 8])->get(['value']); //服务项
        return view('member.plan.create', compact('member', 'projectList'));
    }

    /*
    * 保存到数据库
    * */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            //先判断主播选项是否已经有记录了
            $count = $this->repository->findWhere(['member_id' => $data['member_id'], 'project' => $data['project']])->count();
            if ($count > 0) {
                return $this->failure(1, '该会员[' . $data['project'] . ']已经存在内容，不能重复添加！');
            }
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
            $member = MemberInfo::where(['status' => 1, 'sex' => 1, 'business_check' => 1])->get(['id', 'code', 'user_name', 'nick_name']); //主播会员
            $projectList = SystemData::where(['status' => 1, 'type' => 8])->get(['value']); //服务项
            return view('member.plan.edit', compact('member', 'projectList'))->with('cons', $model);
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
            $old = $this->repository->find($data['id']);
            //修改了要进行验证
            if ($old['project'] != $data['project']) {
                $count = $this->repository->findWhere(['member_id' => $data['member_id'], 'project' => $data['project']])->count();
                if ($count > 0) {
                    return $this->failure(1, '该会员[' . $data['project'] . ']已经存在内容！');
                }
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
            //先删除所有图片
            MemberPlanPic::where('plan_id', $request->ids)->delete();
            $result = $this->repository->destroy($request->ids);
            return $result;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }


}
