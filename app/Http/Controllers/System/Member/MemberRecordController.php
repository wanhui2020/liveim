<?php
/**
 *  会员资金流水管理控制器
 */

namespace App\Http\Controllers\System\Member;

use App\Http\Controllers\Controller;
use App\Repositories\MemberRecordRepository;
use App\Utils\SelectList;
use Illuminate\Http\Request;

class MemberRecordController extends Controller
{
    public function __construct(MemberRecordRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        $recordAccountType = SelectList::recordAccountType();
        $status = SelectList::recordStatus();
        $type = SelectList::recordType();
        return view('member.record.index', compact('recordAccountType', 'status', 'type'));
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