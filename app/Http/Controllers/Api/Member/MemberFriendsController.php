<?php

namespace App\Http\Controllers\Api\Member;

use App\Facades\ImFacade;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Member\MemberFriendsResource;
use App\Models\MemberFriends;
use App\Repositories\MemberFriendsRepository;
use App\Repositories\MemberInfoRepository;
use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

//会员好友管理
class MemberFriendsController extends ApiController
{

    public function __construct(MemberFriendsRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 会员添加好友
     * */
    public function add(Request $request, MemberInfoRepository $memberInfoRepository)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $memberId = $member->id;
            $toid = $request['toid']; //添加对象ID
            if (!isset($toid)) {
                return $this->validation('请输入所有必填参数！');
            }
            if ($memberId == $toid) {
                return $this->validation('不能添加自己为好友！');
            }
            $zbinfo = $memberInfoRepository->find($toid);
            if ($zbinfo == null || $zbinfo['status'] == 0) {
                return $this->validation('添加好友对象不存在！');
            }
            //先判断双方是否已是好友
            $counts = DB::select('select count(*) as counts from member_friends where (member_id=' . $memberId . ' and to_member_id=' . $toid . ' and deleted_at is null and status<>2) or (member_id=' . $toid . ' and to_member_id=' . $memberId . ' and deleted_at is null and status<>2);')[0]->counts;
            if ($counts == 2) {
                return $this->validation('双方已是好友关系！');
            }
            $counts = $this->repository->findWhere(['member_id' => $memberId, 'to_member_id' => $toid, 'status' => 0])->count();
            if ($counts > 0) {
                return $this->validation('已发送好友申请，等待对方确认！');
            }
            //判断对方是否发送了好友申请的
            $counts = $this->repository->findWhere(['member_id' => $toid, 'to_member_id' => $memberId, 'status' => 0])->count();
            if ($counts > 0) {
                return $this->validation('对方已发送添加好友申请，请先确认！');
            }
            $data['member_id'] = $memberId;
            $data['to_member_id'] = $toid;
            $result = $this->repository->store($data);
            if (!$result['status']) {
                return $this->validation('发送好友申请失败！');
            }
            //发送添加好友的通知
            $param = array(
                'key' => 'friend',
                'code' => $member->code,
                'nick_name' => $member->nick_name,
                'pic' => $member->head_pic
            );
            ImFacade::addRoom((string)$zbinfo['code'], $param);

            return $this->succeed(null, '发送好友申请成功！');
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     * 我的好友申请列表
     * */
    public function applyList(Request $request, MemberInfoRepository $memberInfoRepository)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $memberId = $member->id;
            $addWhere = array();
            //默认条件
            $addWhere['to_member_id'] = $memberId;
//            $addWhere['status'] = 0;
            $noun = $this->repository->lists($addWhere, ['member', 'tomember']);
            return $this->succeed(MemberFriendsResource::collection($noun));

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     * 我的好友列表
     * */
    public function myList(Request $request, MemberInfoRepository $memberInfoRepository)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $memberId = $member->id;
            $addWhere = array();
            //默认条件
            $addWhere['member_id'] = $memberId;
            $addWhere['status'] = 1;
            $request['view_type'] = 'my';
            $noun = $this->repository->lists($addWhere, ['tomember']);
            return $this->succeed(MemberFriendsResource::collection($noun));

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     * 好友申请处理
     * */
    public function applyDo(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $id = $request['id']; //记录ID
            $type = $request['type']; //处理类型 1.通过 2拒绝
            if (!isset($id)) {
                return $this->validation('请输入所有必填参数！');
            }
            if (!in_array($type, [1, 2])) {
                return $this->validation('处理类型参数错误！');
            }
            $data = $this->repository->find($id);
            if ($data['status'] != 0) {
                return $this->validation('该申请已处理过！');
            }
            $data['status'] = $type;
            $data['deal_time'] = Helper::getNowTime();
            $result = $this->repository->update($data);
            if (!$result['status']) {
                return $this->validation('好友申请处理失败！');
            }
            //成功,一条好友记录
            if ($type == 1) {
                $ndata['member_id'] = $data['to_member_id'];
                $ndata['to_member_id'] = $data['member_id'];
                $ndata['status'] = 1;
                $ndata['deal_time'] = Helper::getNowTime();
                $this->repository->store($ndata);
                return $this->succeed(null, '通过好友申请！');
            }
            return $this->succeed(null, '拒绝好友申请！');

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     * 删除好友
     * */
    public function delete(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $toid = $request['toid']; //删除好友ID
            if (!isset($toid)) {
                return $this->validation('请输入所有必填参数！');
            }
            MemberFriends::where('member_id', $toid)->orWhere('to_member_id', $toid)->delete();
            return $this->succeed();
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

}
