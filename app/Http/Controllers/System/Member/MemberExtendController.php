<?php
/**
 *  会员扩展信息(主播信息)控制器
 */

namespace App\Http\Controllers\System\Member;

use App\Http\Controllers\Controller;
use App\Repositories\MemberExtendRepository;
use Illuminate\Http\Request;

class MemberExtendController extends Controller
{
    public function __construct(MemberExtendRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        return view('member.extend.index');
    }

    /*
    *  显示列表(获取数据)
    * */
    public function lists(Request $request)
    {
        try {
            $request['sex'] = 1;
            $list = $this->repository->lists(null, ['member']);
            return $this->paginate($list);
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
            return view('member.extend.edit')->with('cons', $model);
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