<?php
/**
 *系统消息
 */

namespace App\Http\Controllers\System\Platform;

use App\Http\Controllers\Controller;
use App\Http\Repositories\SystemMessageRepository;
use App\Models\MemberInfo;
use App\Utils\SelectList;
use Illuminate\Http\Request;

class SystemMessageController extends Controller
{
    public function __construct(SystemMessageRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        $type = SelectList::messageType();
        return view('system.platform.message.index', compact('type'));
    }

    /*
    * 倍数费率显示列表
    * */
    public function lists()
    {
        try {
            $list = $this->repository->lists(null, ['tomember', 'member']);
            return $this->paginate($list);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /*
     * 添加策略产品渲染视图
     * */
    public function create()
    {
        $type = SelectList::messageType();
        $member = MemberInfo::where(['status' => 1])->get(['id', 'code', 'nick_name']); //发起会员
        return view('system.platform.message.create', compact('type', 'member'));
    }

    /*
    * 添加产品策略到数据库
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
            return view('system.platform.message.edit')->with('cons', $cons);
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
     * 删除创建的
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