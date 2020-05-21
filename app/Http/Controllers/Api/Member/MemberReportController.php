<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Api\ApiController;
use App\Repositories\MemberInfoRepository;
use App\Repositories\MemberReportRepository;
use Illuminate\Http\Request;

//会员举报
class MemberReportController extends ApiController
{

    public function __construct(MemberReportRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 会员举报主播
     * */
    public function addReport(Request $request, MemberInfoRepository $memberInfoRepository)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $memberId = $member->id;
            $zbid = $request['zbid']; //主播Id
            $type = $request['type']; //举报类型
            $explain = $request['reason']; //举报原因
            if (!isset($zbid) || !isset($type)) {
                return $this->validation('请输入所有必填参数！');
            }
            $zbinfo = $memberInfoRepository->findWhere(['id' => $zbid, 'sex' => 1])->first();
            if ($zbinfo == null) {
                return $this->validation('主播不存在！');
            }
            //计算价值
            $data['member_id'] = $memberId;
            $data['to_member_id'] = $zbid;
            $data['type'] = $type; //举报类型
            $data['explain'] = $explain; //原因
            $result = $this->repository->store($data);
            if (!$result['status']) {
                return $this->failure($result['msg']);
            }
            return $this->succeed();
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

}
