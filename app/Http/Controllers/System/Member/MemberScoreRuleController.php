<?php

namespace App\Http\Controllers\System\Member;

use App\Http\Controllers\Controller;
use App\Http\Repositories\MemberScoreRuleRepository;
use App\Utils\SelectList;
use Illuminate\Http\Request;

class MemberScoreRuleController extends Controller
{
    public function __construct(MemberScoreRuleRepository $repository)
    {
        $this->repository = $repository;
    }

    //页面首页
    public function index()
    {
        $typeList = SelectList::scoreType();
        $statusList = SelectList::statusList();
        $descList = SelectList::scoreRuleType();
        return view('member.score.rule.index', compact('typeList', 'statusList', 'descList'));
    }

    /*
     * 显示列表
     * */
    public function lists()
    {
        try {
            $list = $this->repository->lists();
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


    /*
     * 添加视图
     * */
    public function create()
    {
        $typeList = SelectList::scoreType();
        $descList = SelectList::DescListByType();
        return view('member.score.rule.create', compact('typeList', 'descList'));
    }

    /*
     * 添加数据库
     * */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            //同一类型不能添加相同描述记录
            $nowType = $request->type;
            $nowDesc = $request->desc;
            $res = $this->repository->findWhere(['type' => $nowType, 'desc' => $nowDesc, 'remark' => $data['remark']])->first();
            if ($res != null) {
                return $this->failure(1, '规则类型[' . SelectList::scoreType()[$nowType] . ']中已存在描述：' . SelectList::scoreRuleType()[$nowDesc]);
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
     * 渲染修改界面
     * */
    public function edit(Request $request)
    {
        try {
            $typeList = SelectList::scoreType();
            $cons = $this->repository->find($request->id);
            $descList = SelectList::DescListByType($cons->type);
            return view('member.score.rule.edit', compact('typeList', 'descList'))->with('cons', $cons);
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

    /*
     * 通过类型查询描述
     * */
    public function getDescList(Request $request)
    {
        $type = $request['type'];
        return SelectList::DescListByType($type);
    }

}
