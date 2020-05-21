<?php

namespace App\Http\Controllers\Api\Member;

use App\Facades\MemberFacade;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Member\MemberAccountResource;
use App\Http\Resources\Member\MemberCoatOrderResource;
use App\Http\Resources\Member\MemberCoatResource;
use App\Http\Resources\Member\MemberFileResource;
use App\Models\MemberCoatOrder;
use App\Models\MemberFile;
use App\Models\MemberFileView;
use App\Repositories\MemberCoatOrderRepository;
use App\Repositories\MemberCoatRepository;
use App\Repositories\MemberFileRepository;
use App\Repositories\MemberInfoRepository;
use App\Services\MemberService;
use Illuminate\Http\Request;

//主播会员衣服库管理
class MemberCoatController extends ApiController
{

    public function __construct(MemberCoatRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 主播衣服列表（会员查看）
     * */
    public function lists(Request $request, MemberInfoRepository $memberInfoRepository)
    {
        try {
            //必须登录会员才能查看
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            $zbid = $request['zbid']; //主播Id
            if (empty($zbid)) {
                return $this->validation('主播ID不能为空！');
            }
            $zbinfo = $memberInfoRepository->findWhere(['id' => $zbid, 'sex' => 1])->first();
            if ($zbinfo == null) {
                return $this->validation('未找到主播！');
            }
            $list = $this->repository->orderBy('sort', 'desc')->findWhere(['member_id' => $zbid, 'status' => 1]);
            return $this->succeed(MemberCoatResource::collection($list));
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     * 我的衣服(主播自己查看)
     * */
    public function myLists(Request $request)
    {
        try {
            //必须登录会员才能查看
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            $where = array(
                'member_id' => $member->id
            );
            $list = $this->repository->orderBy('sort', 'desc')->findWhere($where);
            return $this->succeed(MemberCoatResource::collection($list));
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     * 主播上传衣服照片
     * */
    public function addCoat(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            $memberId = $member->id;
            $title = $request['title']; //衣服标题
            $url = $request['url']; //衣服地址
            if (!isset($title) || !isset($url)) {
                return $this->validation('传入必填参数值！');
            }
            if ($member->sex == 0 || $member->selfie_check == 0) {
                return $this->validation('只有认证主播才能上传衣服！');
            }
            $count = $this->repository->findWhere(['member_id' => $memberId, 'title' => $title])->count();
            if ($count > 0) {
                return $this->validation('衣服标题已经存在！');
            }
            $data['member_id'] = $memberId;
            $data['title'] = $title;
            $data['url'] = $url;
            $data['status'] = 1;
            $result = $this->repository->store($data);
            if (!$result['status']) {
                return $this->validation('添加衣服失败！');
            }
            return $this->succeed(null, '添加衣服成功！');
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }

    }

    /*
    * 主播删除衣服
    * */
    public function deleteCoat(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $coatid = $request['coatid']; //删除衣服
            if (!isset($coatid)) {
                return $this->validation('请输入所有必填参数！');
            }
            $viewCounts = MemberCoatOrder::where(['member_coat_id' => $coatid])->where('status', '<', 3)->count();
            if ($viewCounts > 0) {
                return $this->failure(1, '该衣服还有未完结订单，不能删除！');
            }
            $this->repository->destroy([$coatid]);
            return $this->succeed();
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     * 会员申请换衣订单
     * */
    public function addOrder(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            //可修改字段
            $coatid = $request['coatid']; //衣服ID
            if (empty($coatid)) {
                return $this->validation('参数错误，请传入必填参数值！');
            }
            $coat = $this->repository->find($coatid);
            if (!isset($coat) || $coat->status == 0) {
                return $this->validation('衣服不存在！');
            }
            $gold = $coat->member->extend->coat_fee; //换衣收费
            //添加资源库查看记录
            $coatModel = new MemberCoatOrder();
            $coatModel['member_id'] = $member->id;
            $coatModel['to_member_id'] = $coat->member_id; //所属主播
            $coatModel['member_coat_id'] = $coatid; //
            $coatModel['gold'] = $gold; //
            return MemberFacade::addCoatOrder($coatModel); //调用查看方法
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }

    }

    /*
     * 我的换衣订单列表
     * */
    public function myOrderList(Request $request, MemberCoatOrderRepository $memberCoatOrderRepository)
    {
        try {
            //必须登录会员才能查看
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            $sex = $member->sex;
            $addwhere = array();
            if ($sex == 1) {
                //主播查询
                $addwhere['to_member_id'] = $member->id;
            } else {
                $addwhere['member_id'] = $member->id;
            }
            $list = $memberCoatOrderRepository->lists($addwhere, ['coat', 'member', 'tomember']);
            $request['view_type'] = 'list';
            return $this->succeed(MemberCoatOrderResource::collection($list));
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }


    /*
     * 换衣订单详情
     * */
    public function coatOrderInfo(Request $request, MemberCoatOrderRepository $memberCoatOrderRepository)
    {
        try {
            //必须登录会员才能查看
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            $orderid = $request['orderid'];
            if (!isset($orderid)) {
                return $this->validation('参数错误，请传入必填参数值！');
            }
            $order = $memberCoatOrderRepository->find($orderid);
            if (!isset($order)) {
                return $this->validation('未找到订单！');
            }
            return $this->succeed(new MemberCoatOrderResource($order));
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
    * 换衣订单处理
    * */
    public function dealCoatOrder(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $id = $request['orderid']; //记录ID
            $status = $request['status']; //订单处理状态
            if (!isset($id) || !isset($status)) {
                return $this->validation('请输入所有必填参数！');
            }
            if (!in_array($status, [1, 2, 3, 4, 5])) {
                return $this->validation('处理状态参数错误！');
            }
            if ($member->sex == 1 && !in_array($status, [1, 2, 5])) {
                return $this->validation('处理状态参数错误！');
            }
            if ($member->sex == 0 && !in_array($status, [3, 4])) {
                return $this->validation('处理状态参数错误！');
            }
            return MemberFacade::dealCoatOrder($id, $status); //调用处理方法

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }


}
