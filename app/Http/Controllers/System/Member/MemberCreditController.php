<?php
/**
 *  会员信誉评价管理控制器
 */

namespace App\Http\Controllers\System\Member;

use App\Http\Controllers\Controller;
use App\Models\MemberCredit;
use App\Models\MemberInfo;
use App\Models\SystemData;
use App\Repositories\MemberCreditRepository;
use App\Utils\Helper;
use App\Utils\SelectList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberCreditController extends Controller
{
    public function __construct(MemberCreditRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        return view('member.credit.index');
    }

    /*
    *  显示列表(获取数据)
    * */
    public function lists()
    {
        try {
            $list = $this->repository->lists(null, ['member', 'tomember']);
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
        $member = MemberInfo::where(['status' => 1, 'sex' => 0])->get(['id', 'code', 'user_name', 'nick_name']); //会员
        $tomember = MemberInfo::where(['status' => 1, 'sex' => 1])->get(['id', 'code', 'user_name', 'nick_name']); //举报会员
        return view('member.credit.create', compact('member', 'tomember'));
    }

    /*
    * 保存到数据库
    * */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $result = $this->repository->store($data);
            return $this->failure(1, $result['msg']);
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