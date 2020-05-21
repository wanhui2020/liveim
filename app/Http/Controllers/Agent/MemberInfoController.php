<?php
/**
 *  会员信息控制器
 */

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Repositories\MemberInfoRepository;
use App\Repositories\MemberInviteAwardRepository;
use App\Utils\SelectList;
use Illuminate\Support\Facades\Auth;

class MemberInfoController extends Controller
{
    public $inviterRepository;

    public function __construct(MemberInfoRepository $repository, MemberInviteAwardRepository $inviteAwardRepository)
    {
        $this->repository = $repository;
        $this->inviterRepository = $inviteAwardRepository;

    }

    /*
      * 直接下级会员列表
      * */
    public function subindex()
    {
        return view('agent.sub.index');
    }

    /*
    *  显示列表(获取数据)
    * */
    public function sublists()
    {
        try {
            $agentId = Auth::guard('agent')->user()->id;
            $where = ['agent_id' => $agentId];
            $list = $this->repository->lists($where, ['account', 'parent', 'lastlogin']);
            return $this->paginate($list);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /*
      * 提成收入明细
      * */
    public function income()
    {
        return view('agent.sub.income');
    }

    /*
    *  显示列表(获取数据)
* */
    public function incomeLists()
    {
        try {
            $agentId = Auth::guard('agent')->user()->id;
            $where = ['member_id' => $agentId];
            $list = $this->inviterRepository->lists($where, ['member', 'frommember']);
            return $this->paginate($list);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

}
