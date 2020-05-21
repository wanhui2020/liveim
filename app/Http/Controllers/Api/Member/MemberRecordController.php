<?php

namespace App\Http\Controllers\Api\Member;

use App\Facades\MemberFacade;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Member\MemberRecordResource;
use App\Models\MemberExchange;
use App\Models\MemberTakeNow;
use App\Repositories\MemberRecordRepository;
use App\Repositories\MemberTakeNowRepository;
use Illuminate\Http\Request;

/*
 * 会员资金流水管理
 * */

class MemberRecordController extends ApiController
{

    public function __construct(MemberRecordRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 会员资金流水
     * @param Request $request
     * @return array
     */
    public function lists(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $addWhere['member_id'] = $member->id;
            $noun = $this->repository->orderBy('created_at', 'desc')->lists($addWhere);
            return $this->succeed(MemberRecordResource::collection($noun));
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }
}
