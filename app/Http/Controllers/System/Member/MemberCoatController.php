<?php
/**
 *  主播会员衣服管理控制器
 */

namespace App\Http\Controllers\System\Member;

use App\Facades\RecordFacade;
use App\Http\Controllers\Controller;
use App\Models\MemberAccount;
use App\Models\MemberCoatOrder;
use App\Models\MemberExtend;
use App\Models\MemberFileView;
use App\Models\MemberInfo;
use App\Models\MemberRecord;
use App\Repositories\MemberCoatRepository;
use App\Repositories\MemberInfoRepository;
use App\Utils\Helper;
use App\Utils\SelectList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MemberCoatController extends Controller
{
    public function __construct(MemberCoatRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        $status = SelectList::statusList();
        return view('member.coat.index', compact('status'));
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
     * 添加视图
     * */
    public function create()
    {
        $member = MemberInfo::where(['status' => 1, 'sex' => 1])->get(['id', 'code', 'user_name', 'nick_name']); //主播会员
        return view('member.coat.create', compact('member'));
    }

    /*
    * 保存到数据库
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
    * 修改界面
    * */
    public function edit(Request $request)
    {
        try {
            $model = $this->repository->find($request->id);
            $member = MemberInfo::where(['status' => 1, 'sex' => 1])->get(['id', 'code', 'user_name', 'nick_name']); //主播会员
            return view('member.coat.edit', compact('member'))->with('cons', $model);
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
     * 发起申请订单
     * */
    public function addOrder(Request $request)
    {

        $member = MemberInfo::where(['status' => 1, 'sex' => 0])->get(['id', 'code', 'user_name', 'nick_name']); //会员
        $model = $this->repository->find($request->id);
        return view('member.coat.addorder', compact('member'))->with('cons', $model);
    }

    /*
     * 申请订单保存
     * */
    public function addOrderSave(Request $request)
    {
        try {
            //查看资源
            $id = $request->id; //衣服ID
            $memberId = $request->member_id; //衣服所属主播ID
            $viewMemberId = $request->view_member_id;//查看会员ID
            //先判断该会员是否有未结束的换衣订单
            $viewCounts = MemberCoatOrder::where(['member_id' => $viewMemberId, 'member_coat_id' => $id])->where('status', '<', 3)->count();
            if ($viewCounts > 0) {
                return $this->failure(1, '该会员还有未结束的换衣订单，不能再提交！');
            }
            //判断主播是否有未结束的换衣订单
            $counts = MemberCoatOrder::where(['to_member_id' => $memberId])->where('status', '<', 3)->count();
            if ($counts > 0) {
                return $this->failure(1, '该主播还有未结束的换衣订单，请更换其他主播！');
            }

            $data = new MemberCoatOrder();
            $data['member_id'] = $viewMemberId;
            $data['to_member_id'] = $memberId;
            $data['member_coat_id'] = $id;
            $data['gold'] = $request->gold;
            $data['status'] = 0; //申请中
            if ($data->save()) {
                return $this->succeed(null, '申请成功！');
            }
            return $this->failure(1, '申请失败！');
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }


}