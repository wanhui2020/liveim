<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Api\ApiController;
use App\Repositories\MemberIdeaRepository;
use Illuminate\Http\Request;

//会员意见反馈
class MemberIdeaController extends ApiController
{

    public function __construct(MemberIdeaRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 会员意见反馈
     * @param Request $request
     * @return array
     */
    public function addIdea(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $memberId = $member->id;
            $content = $request['content'];
            if (!isset($content)) {
                return $this->validation('请输入反馈意见内容！');
            }
            $data['member_id'] = $memberId;
            $data['content'] = $content;
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
