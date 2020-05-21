<?php

namespace App\Http\Controllers\System\Member;

use App\Http\Controllers\Controller;
use App\Http\Repositories\MemberMlScoreRepository;
use App\Repositories\MemberInfoRepository;
use App\Utils\SelectList;
use Illuminate\Http\Request;

class MemberMlScoreController extends Controller
{
    public function __construct(MemberMlScoreRepository $repository)
    {
        $this->repository = $repository;
    }

    //页面首页
    public function index()
    {
        $typeList = SelectList::DescListByType(2);
        return view('member.score.ml.index', compact('typeList'));
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
        $typeList = SelectList::DescListByType(2);
        $member = $memberInfoRepository->findWhere(['status' => 1], ['id', 'code', 'user_name', 'nick_name']); //会员
        return view('member.score.ml.create', compact('typeList', 'member'));
    }

    /*
     * 添加数据库
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
     * 渲染修改界面
     * */
    public function edit(Request $request)
    {
        try {
            $typeList = SelectList::DescListByType(2);
            $cons = $this->repository->find($request->id);
            return view('member.score.ml.edit', compact('typeList'))->with('cons', $cons);
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
