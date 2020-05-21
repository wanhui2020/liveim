<?php

namespace App\Http\Controllers\System\Platform;

use App\Http\Controllers\Controller;
use App\Http\Repositories\SystemDataRepository;
use App\Http\Repositories\SystemFileRepository;
use App\Models\SystemData;
use App\Utils\SelectList;
use Illuminate\Http\Request;


class SystemFileController extends Controller
{
    public function __construct(SystemFileRepository $repository)
    {
        $this->repository = $repository;
    }

    //页面首页
    public function index()
    {
        return view('system.platform.file.index');
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

    /*
     * 添加视图
     * */
    public function create()
    {
        return view('system.platform.file.create');
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
            $cons = $this->repository->find($request->id);
            return view('system.platform.file.edit')->with('cons', $cons);
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
            if ($result['status']) {
                //数据删除，需执行删除文件操作

                return $this->succeed($result);
            }
            return $result;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }
}
