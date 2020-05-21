<?php

namespace App\Http\Controllers\System\Member;

use App\Facades\MemberFacade;
use App\Http\Controllers\Controller;
use App\Models\MemberInfo;
use App\Models\SystemTag;
use App\Repositories\MemberTagsRepository;
use Illuminate\Http\Request;

/*
 * 会员分类标签管理
 * */

class MemberTagsController extends Controller
{
    public function __construct(MemberTagsRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        return view('member.tag.index');
    }

    /*
    *  显示列表(获取数据)
    * */
    public function lists()
    {
        try {
            $list = $this->repository->lists(null, ['member', 'tag']);
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
        $member = MemberInfo::where(['status' => 1, 'sex' => 1])->get(['id', 'code', 'user_name', 'nick_name']); //主播会员
        $tags = SystemTag::where(['status' => 1, 'is_sys' => 1])->orderBy('sort', 'desc')->get(['id', 'name']);
        return view('member.tag.create', compact('member', 'tags'));
    }

    /*
    * 保存到数据库
    * */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $tagStr = rtrim($request['tagstr'], ',');
            if ($tagStr != '') {
                MemberFacade::memberToTag($data['member_id'], $tagStr);
            }
            return $this->succeed();
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
            return view('member.tag.edit')->with('cons', $cons);
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
            $result = $this->repository->forceDelete($request->ids);
            return $result;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }


}
