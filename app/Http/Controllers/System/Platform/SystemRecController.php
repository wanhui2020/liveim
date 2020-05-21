<?php

namespace App\Http\Controllers\System\Platform;

use App\Http\Controllers\Controller;
use App\Http\Repositories\SystemRecRepository;
use App\Models\SystemData;
use App\Models\SystemRec;
use App\Utils\SelectList;
use Illuminate\Http\Request;

class SystemRecController extends Controller
{
    public function __construct(SystemRecRepository $repository)
    {
        $this->repository = $repository;
    }

    //页面首页
    public function index()
    {
        $typeList = SelectList::recType();
        $statusList = SelectList::statusList();
        return view('system.platform.rec.index', compact('typeList', 'statusList'));
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
        $typeList = SelectList::recType();
        return view('system.platform.rec.create', compact('typeList'));
    }

    /*
     * 添加数据库
     * */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            //同一类型不能添加相同的名称
            $nowType = $request->type;
            $nowName = $request->name;
            $res = SystemRec::where(['type' => $nowType, 'name' => $nowName])->first();
            if ($res != null) {
                return $this->failure(1, '类型[' . SelectList::recType()[$nowType] . ']中已存在名称：' . $nowName);
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
            $typeList = SelectList::recType();
            $cons = $this->repository->find($request->id);
            return view('system.platform.rec.edit', compact('typeList'))->with('cons', $cons);
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
