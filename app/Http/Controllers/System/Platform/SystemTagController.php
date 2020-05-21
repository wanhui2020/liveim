<?php

namespace App\Http\Controllers\System\Platform;

use App\Http\Controllers\Controller;
use App\Http\Repositories\SystemTagRepository;
use App\Models\SystemTag;
use App\Utils\SelectList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class SystemTagController extends Controller
{
    public function __construct(SystemTagRepository $repository)
    {
        $this->repository = $repository;
    }

    //页面首页
    public function index()
    {
        $statusList = SelectList::statusList();
        return view('system.platform.tag.index', compact('statusList'));
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
        return view('system.platform.tag.create');
    }

    /*
     * 添加数据库
     * */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $nowName = $data['name'];
            $res = SystemTag::where(['name' => $nowName])->first();
            if ($res != null) {
                return $this->failure(1, '标签名称[' . $nowName . ']已存在！');
            }
            $data['is_sys'] = 1; //后台添加，就是系统默认的
            $data['status'] = 1; //默认开启
            $data['create_user'] = Auth::user()->name; //添加人
            $result = $this->repository->store($data);
            if ($result['status']) {
                return $this->succeed($result);
            }
            return $this->failure(1, $result['msg']);
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
     * 渲染修改界面
     * */
    public function edit(Request $request)
    {
        try {
            $cons = $this->repository->find($request->id);
            return view('system.platform.tag.edit')->with('cons', $cons);
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
            return $this->succeed($result);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }
}
