<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Member\MemberAccountResource;
use App\Repositories\MemberAccountRepository;
use App\Utils\Helper;
use Illuminate\Http\Request;

class MemberAccountController extends ApiController
{

    public function __construct(MemberAccountRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 会员账户信息
     * @param Request $request
     * @return array
     */
    public function account(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            $account = $this->repository->findBy('member_id', $member->id);
            return $this->succeed(new MemberAccountResource($account), '操作成功');
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }


}
