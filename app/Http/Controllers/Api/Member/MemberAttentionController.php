<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Member\MemberAttentionResource;
use App\Http\Resources\Member\MemberInfoResource;
use App\Models\MemberAttention;
use App\Repositories\MemberAttentionRepository;
use App\Repositories\MemberInfoRepository;
use Illuminate\Http\Request;

//会员关注
class MemberAttentionController extends ApiController
{

    public function __construct(MemberAttentionRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 会员关注/取消关注
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
            $toid = $request['toid']; //关注对象ID
            $type = $request['type']; //类型：0取消关注 1关注
            if (!isset($toid) || !isset($type)) {
                return $this->validation('请输入所有必填参数！');
            }

            $zbinfo = $memberInfoRepository->find($toid);
            if ($zbinfo == null || $zbinfo['status'] == 0) {
                return $this->validation('关注对象不存在！');
            }
            if ($type == 0) {
                //取消关注
                MemberAttention::where(['member_id' => $memberId, 'to_member_id' => $toid])->delete();
                return $this->succeed(null, '已取消关注！');
            }
            //添加关注
            $data['member_id'] = $memberId;
            $data['to_member_id'] = $toid;
            $result = $this->repository->store($data);
            if (!$result['status']) {
                return $this->failure('关注失败！');
            }
            return $this->succeed(null, '已关注！');
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     * 会员关注列表
     * */
    public function lists(Request $request, MemberInfoRepository $memberInfoRepository)
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

            $noun = $this->repository->lists($addWhere, ['tomember']);
            return $this->succeed(MemberAttentionResource::collection($noun));

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

}
