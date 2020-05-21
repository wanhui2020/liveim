<?php
/**
 *  会员账户信息控制器
 */

namespace App\Http\Controllers\System\Member;

use App\Http\Controllers\Controller;
use App\Models\MemberInfo;
use App\Repositories\MemberAccountRepository;
use Illuminate\Http\Request;

class MemberAccountController extends Controller
{
    public function __construct(MemberAccountRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        return view('member.account.index');
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

    /*
     * 修改界面
     * */
    public function edit(Request $request)
    {
        try {
            $model = $this->repository->find($request->id);
            return view('member.account.edit')->with('cons', $model);
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

    /*
     * 会员赠送VIP
     *  */
    public function setVip(Request $request)
    {
        $account = $this->repository->find($request->id);
        return view('member.account.setvip', compact('account'));
    }

}