<?php

namespace App\Http\Controllers\System\Platform;

use App\Http\Controllers\Controller;
use App\Http\Repositories\SystemDataRepository;
use App\Models\SystemData;
use App\Utils\SelectList;
use Illuminate\Http\Request;

class SystemDataController extends Controller
{
    public function __construct(SystemDataRepository $repository)
    {
        $this->repository = $repository;
    }

    //页面首页
    public function index()
    {
        $typeList = SelectList::dataTypeList();
        $statusList = SelectList::statusList();
        return view('system.platform.data.index', compact('typeList', 'statusList'));
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
        $typeList = SelectList::dataTypeList();
        return view('system.platform.data.create', compact('typeList'));
    }

    /*
     * 添加数据库
     * */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            unset($data['file']);
            //同一类型不能添加相同的键或值
            $nowKey = $request->key;
            $nowValue = $request->value;
            $nowType = $request->type;

            $res = SystemData::where(['type' => $nowType, 'key' => $nowKey])->first();
            if ($res != null) {
                return $this->failure(1, '数据类型[' . SelectList::dataTypeList()[$nowType] . ']中已存在键：' . $nowKey);
            }
            $res = SystemData::where(['type' => $nowType, 'value' => $nowValue])->first();
            if ($res != null) {
                return $this->failure(1, '数据类型[' . SelectList::dataTypeList()[$nowType] . ']中已存在值：' . $nowValue);
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
            $typeList = SelectList::dataTypeList();
            $cons = $this->repository->find($request->id);
            return view('system.platform.data.edit', compact('typeList'))->with('cons', $cons);
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
            unset($data['file']);
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
